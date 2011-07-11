<?php
/*
 * create_instance.php
 *
 * Use the information in the metadata table to set up a new db instance
 *
 */
session_start();

// Are we authorized to view this page?
if ( ! isset($_SESSION['id']) )
{
  header('Location: index.php');
  exit();
} 

if ( ($_SESSION['userlevel'] != 4) &&
     ($_SESSION['userlevel'] != 5) )    // admin and super admin only
{
  header('Location: index.php');
  exit();
} 

include 'config.php';
include 'db.php';
include 'lib/utility.php';

// Start displaying page
$page_title = "Create DB Instance";
include 'header.php';
?>
<!-- Begin page content -->
<div id='content'>

  <h1 class="title">Create DB Instance</h1>
  <!-- Place page content here -->

<?php
  if ( isset( $_POST['step_3'] ) )
    do_step3();

  else if ( isset( $_POST['step_2'] ) )
    do_step2();

  else if ( isset( $_SESSION['metadataID'] ) )
    do_step1();

  else
    echo "<p>Error: you need to execute this program from " .
         "<a href='view_metadata.php'>here</a>.</p>\n";

?>
</div>

<?php
include 'footer.php';
exit();

function do_step1()
{
  // First time here
  $metadataID = $_SESSION['metadataID'];
  unset( $_SESSION['metadataID'] );

  // Double check if this has been done before
  $query  = "SELECT status FROM metadata " .
            "WHERE metadataID = $metadataID ";
  $result = mysql_query($query) 
            or die("Query failed : $query<br />\n" . mysql_error());

  if ( mysql_num_rows( $result ) != 1 ) 
  {
    error( "Error: metadata record not found." );
    return;
  }

  $status = '';
  list( $status ) = mysql_fetch_array( $result );
  if ( $status == 'completed' )
  {
    error( "Error: this database has already been set up." );
    return;
  }

  $query  = "SELECT institution, dbname, dbuser, dbpasswd, dbhost " .
            "FROM metadata " .
            "WHERE metadataID = $metadataID ";
  $result = mysql_query($query) 
            or die("Query failed : $query<br />\n" . mysql_error());

  list( $institution,
        $new_dbname,
        $new_dbuser,
        $new_dbpasswd,
        $new_dbhost )    = mysql_fetch_array( $result );

  echo <<<HTML
  <p>Step 1</p>

  <ul><li>Create database for $institution:<br />
          CREATE DATABASE $new_dbname;</li>
      <li>Enable the LIMS user<br />
          GRANT ALL ON $new_dbname.* TO $new_dbuser@localhost IDENTIFIED BY '$new_dbpasswd';</li>
          GRANT ALL ON $new_dbname.* TO $new_dbuser@'%' IDENTIFIED BY '$new_dbpasswd';</li>
          GRANT EXECUTE ON $new_dbname.* TO us3secure@'%' REQUIRE SSL;
      <li>Load the database definition:<br />
          mysql -u root -p $new_dbname &lt; us3.sql</li>
      <li>Load the stored procedures:<br />
          mysql -u root -p $new_dbname &lt; us3_procedures.sql</li>
      <li>Click Next--&gt;</li>
  </ul>

  <form action={$_SERVER['PHP_SELF']} method='post' >
    <input type='submit' name='step_2' value='Next--&gt;' />
    <input type='hidden' name='metadataID' value='$metadataID' />
  </form>

HTML;
}

function do_step2()
{
  $metadataID = $_POST['metadataID'];

  $query  = "SELECT institution, dbname, dbuser, dbpasswd, dbhost " .
            "FROM metadata " .
            "WHERE metadataID = $metadataID ";
  $result = mysql_query($query) 
            or die("Query failed : $query<br />\n" . mysql_error());

  if ( mysql_num_rows( $result ) != 1 ) return;

  list( $institution,
        $new_dbname,
        $new_dbuser,
        $new_dbpasswd,
        $new_dbhost )    = mysql_fetch_array( $result );

  echo <<<HTML
  <p>Step 2</p>

  <ul><li>Check out LIMS code for $institution:<br />
          svn co svn+ssh://username@bcf.uthscsa.edu/us3_lims/trunk $new_dbname</li>
      <li>Create the LIMS configuration file:<br />
          cp config.php.template config.php</li>
      <li>Create the LIMS data directory<br />
          mkdir data<br />
          chmod 0777 data</li>
      <li>Update configuration with this information:<br />
          <table cellspacing='0' cellpadding='3' style='text-align:left;'>
            <tr><th>Database name:</th><td>$new_dbname</td></tr>
            <tr><th>Database user:</th><td>$new_dbuser</td></tr>
            <tr><th>DB User Password:</th><td>$new_dbpasswd</td></tr>
            <tr><th>Server name:</th><td>$new_dbhost</td></tr>
            <tr><th>Global DB User:</th><td>gfac</td></tr>
            <tr><th>Global DB password:</th><td>backend</td></tr>
            <tr><th>Global DB name:</th><td>gfac</td></tr>
            <tr><th>Global DB host:</th><td>ultrascan3.uthscsa.edu</td></tr>
          </table>
          Replace all instances of &ldquo;lims3&rdquo; with $new_dbname</li>
      <li>Click Next--&gt;</li>
  </ul>

  <form action={$_SERVER['PHP_SELF']} method='post' >
    <input type='submit' name='step_3' value='Next--&gt;' />
    <input type='hidden' name='metadataID' value='$metadataID' />
  </form>

HTML;
}

function do_step3()
{
  $metadataID = $_POST['metadataID'];

  // Let's just get everything we're going to need
  $query  = "SELECT institution, dbname AS new_dbname, dbuser AS new_dbuser, " .
            "dbpasswd AS new_dbpasswd, dbhost AS new_dbhost, " .
            "admin_fname, admin_lname, admin_email, admin_pw, " .
            "lab_name, lab_building, lab_room, " .
            "instrument_name, instrument_serial, " .
            "operator_fname, operator_lname, operator_email, operator_pw, " .
            "status " .
            "FROM metadata " .
            "WHERE metadataID = $metadataID ";
  $result = mysql_query($query) 
            or die("Query failed : $query<br />\n" . mysql_error());

  $row    = mysql_fetch_array($result, MYSQL_ASSOC);

  // Create local variables
  foreach ($row as $key => $value)
  {
    $$key = (empty($value)) ? "" : html_entity_decode( stripslashes( nl2br($value) ) );
  }

  // Now switch databases
  $link2 = mysql_connect( $new_dbhost, $new_dbuser, $new_dbpasswd ) 
           or die("Could not connect to database server.");

  mysql_select_db( $new_dbname, $link2 ) 
          or die("Could not select $new_dbname database. " );

  // Administrator record
  $guid = uuid();
  $query  = "INSERT INTO people SET " .
            "personGUID = '$guid', " .
            "fname = '$admin_fname', " .
            "lname = '$admin_lname', " .
            "email = '$admin_email', " .
            "password = '$admin_pw', " .
            "organization = '$institution', " .
            "activated = true, " .
            "userlevel = 4 ";
  mysql_query($query) 
        or die("Query failed : $query<br />\n" . mysql_error());
  $admin_id = mysql_insert_id();

  // Operator record
  $guid = uuid();
  $query  = "INSERT INTO people SET " .
            "personGUID = '$guid', " .
            "fname = '$operator_fname', " .
            "lname = '$operator_lname', " .
            "email = '$operator_email', " .
            "password = '$operator_pw', " .
            "organization = '$institution', " .
            "activated = true, " .
            "userlevel = 3 ";
  mysql_query($query) 
        or die("Query failed : $query<br />\n" . mysql_error());
  $operator_id = mysql_insert_id();

  // The institution's lab
  $guid = uuid();
  $query  = "INSERT INTO lab SET " .
            "labGUID = '$guid', " .
            "name = '$lab_name', " .
            "building = '$lab_building', " .
            "room = '$lab_room', " .
            "dateUpdated = NOW() ";
  mysql_query($query) 
        or die("Query failed : $query<br />\n" . mysql_error());
  $lab_id = mysql_insert_id();

  // The instrument in the lab
  $query  = "INSERT INTO instrument SET " .
            "name = '$instrument_name', " .
            "labID = '$lab_id', " .
            "serialNumber = '$instrument_serial', " .
            "dateUpdated = NOW() ";
  mysql_query($query) 
        or die("Query failed : $query<br />\n" . mysql_error());
  $instrument_id = mysql_insert_id();

  // Set permits for these users to use the instrument
  $query  = "INSERT INTO permits SET " .
            "personID = $admin_id, " .
            "instrumentID = $instrument_id ";
  mysql_query($query) 
        or die("Query failed : $query<br />\n" . mysql_error());

  $query  = "INSERT INTO permits SET " .
            "personID = $operator_id, " .
            "instrumentID = $instrument_id ";
  mysql_query($query) 
        or die("Query failed : $query<br />\n" . mysql_error());

  // Create a channel to be used
  // For now just choose an abstract channel to base it on
  $guid = uuid();
  $query  = "INSERT INTO channel SET " .
            "abstractChannelID = 2, " .        // Epon 2-channel standard
            "channelGUID = '$guid', " .
            "comments = 'Record generated automatically by newlims program.' , " .
            "dateUpdated = NOW() ";
  mysql_query($query) 
        or die("Query failed : $query<br />\n" . mysql_error());

  // Update status in metadata DB
  global $link, $dbhost, $dbusername, $dbpasswd, $dbname;
  $link = mysql_connect( $dbhost, $dbusername, $dbpasswd ) 
          or die("Could not connect to database server.");
  mysql_select_db($dbname, $link) 
          or die("Could not select database $dbname. " );
  $query  = "UPDATE metadata SET " .
            "status = 'completed' " .
            "WHERE metadataID = $metadataID ";
  mysql_query($query) 
        or die("Query failed : $query<br />\n" . mysql_error());

  echo "<p>The database instance has been created.</p>\n";

}

// Function to display an error message
function error( $msg )
{
  echo "<p>$msg</p>\n";
}
?>
