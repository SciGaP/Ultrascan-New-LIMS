<?php
/*
 * edit_metadata.php
 *
 * A place to edit/update/process the metadata table
 *
 */
session_start();

// Are we authorized to view this page?
if ( ! isset($_SESSION['id']) )
{
  header('Location: index.php');
  exit();
} 

if ( ( $_SESSION['userlevel'] != 4 ) &&
     ( $_SESSION['userlevel'] != 5 ) )  // admin and superadmin only
{
  header('Location: index.php');
  exit();
}

include 'config.php';
include 'db.php';
include 'lib/utility.php';

if (isset($_POST['update']))
{
  do_update();
  exit();
}

if ( isset( $_POST['create'] ) )
{
  $metadataID = $_POST['metadataID'];

  // We have to check if all the fields have data before going on.
  $query  = "SELECT institution, dbname, dbuser, dbpasswd, dbhost, " .
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

  $message = '';  
  foreach ($row as $key => $value)
  {
    $value = html_entity_decode( stripslashes( nl2br($value) ) );
    if ( empty( $value ) )
      $message .= "--$$key cannot be empty.<br />";
  }

  if ( ! empty( $message ) )
  {
    $_SESSION['message'] = $message;

    header("Location: $_SERVER[PHP_SELF]?edit=$metadataID");
    exit();
  }

  // Ok to go on
  $_SESSION['metadataID'] = $metadataID;

  header('Location: create_instance.php');
  exit();
}

// Start displaying page
$page_title = 'Process LIMS Instance Requests';
$js = 'js/edit_metadata.js';
include 'top.php';
include 'links.php';
include 'lib/selectboxes.php';

?>
<!-- Begin page content -->
<div id='content'>

  <h1 class="title">Process LIMS Instance Requests</h1>
  <!-- Place page content here -->

<?php

if ( isset( $_SESSION['message'] ) )
{
  echo "<p class='message'>The following errors were noted:</p>\n";
  echo "<p class='message'>{$_SESSION['message']}</p>\n";
  unset( $_SESSION['message'] );
}

// Edit or display a record
if ( isset($_POST['edit']) || isset($_GET['edit']) )
  edit_record();

else
  display_record();

?>
</div>

<?php
include 'bottom.php';
exit();

// Function to update the current record
function do_update()
{
  include 'get_meta_info.php';
  $metadataID   =                                     $_POST['metadataID'];
  $dbname       = trim(substr(addslashes(htmlentities($_POST['dbname'])), 0,30));
  $dbuser       = trim(substr(addslashes(htmlentities($_POST['dbuser'])), 0,30));
  $dbpasswd     = trim(substr(addslashes(htmlentities($_POST['dbpasswd'])), 0,30));
  $dbhost       = trim(substr(addslashes(htmlentities($_POST['dbhost'])), 0,30));
  $admin_pw1    = trim(substr(addslashes(htmlentities($_POST['admin_pw1'])), 0,80));
  $admin_pw2    = trim(substr(addslashes(htmlentities($_POST['admin_pw2'])), 0,80));
  $operator_pw1 = trim(substr(addslashes(htmlentities($_POST['operator_pw1'])), 0,80));
  $operator_pw2 = trim(substr(addslashes(htmlentities($_POST['operator_pw2'])), 0,80));
  $status       =                                     $_POST['status'];

  if ( empty( $dbname ) )
    $message .= "--db name cannot be empty.<br />";

  if ( empty( $dbuser ) )
    $message .= "--db user name cannot be empty.<br />";

  if ( empty( $dbpasswd ) )
    $message .= "--dbpassword cannot be empty.<br />";

  if ( empty( $dbhost ) )
    $message .= "--db host cannot be empty.<br />";

  if ( $admin_pw1 != $admin_pw2 )
    $message .= "--administrator passwords do not match.<br />";

  if ( $operator_pw1 != $operator_pw2 )
    $message .= "--operator passwords do not match.<br />";

  if ( empty( $message ) )
  {
    $admin_pw_text    = ( empty( $admin_pw1 ) ) 
                      ? "" : "admin_pw = MD5('$admin_pw1'), ";
    $operator_pw_text = ( empty( $operator_pw1 ) ) 
                      ? "" : "operator_pw = MD5('$operator_pw1'), ";

    $query = "UPDATE metadata " .
             "SET institution  = '$institution', " .
             "dbname  = '$dbname', " .
             "dbuser  = '$dbuser', " .
             "dbpasswd  = '$dbpasswd', " .
             "dbhost  = '$dbhost', " .
             "admin_fname  = '$admin_fname', " .
             "admin_lname  = '$admin_lname', " .
             "admin_email  = '$admin_email', " .
             $admin_pw_text .
             "lab_name  = '$lab_name', " .
             "lab_building  = '$lab_building', " .
             "lab_room  = '$lab_room', " .
             "instrument_name  = '$instrument_name', " .
             "instrument_serial  = '$instrument_serial', " .
             "operator_fname  = '$operator_fname', " .
             "operator_lname  = '$operator_lname', " .
             "operator_email  = '$operator_email', " .
             $operator_pw_text .
             "status  = '$status', " .
             "updateTime = NOW() " .
             "WHERE metadataID = $metadataID ";

    mysql_query($query)
      or die("Query failed : $query<br />\n" . mysql_error());

    header("Location: $_SERVER[PHP_SELF]?ID=$metadataID");
  }

  else
  {
    $_SESSION['message'] = $message;

    header("Location: $_SERVER[PHP_SELF]?edit=$metadataID");
  }

}

// Function to display and navigate records
function display_record()
{
  // Find a record to display
  $metadataID = get_id();
  if ($metadataID === false)
    return;

  $query  = "SELECT institution, dbname, dbuser, dbpasswd, dbhost, " .
            "admin_fname, admin_lname, admin_email, " .
            "lab_name, lab_building, lab_room, " .
            "instrument_name, instrument_serial, " .
            "operator_fname, operator_lname, operator_email, " .
            "status " .
            "FROM metadata " .
            "WHERE metadataID = $metadataID ";
  $result = mysql_query($query) 
            or die("Query failed : $query<br />\n" . mysql_error());

  $row    = mysql_fetch_array($result, MYSQL_ASSOC);

  // Create local variables; make sure IE displays empty cells properly
  foreach ($row as $key => $value)
  {
    $$key = (empty($value)) ? "&nbsp;" : html_entity_decode( stripslashes( nl2br($value) ) );
  }

  global $request_status;               // From lib/selectboxes.php
  $status = $request_status[ $status ];

echo<<<HTML
  <form action="{$_SERVER['PHP_SELF']}" method='post'>
  <table cellspacing='0' cellpadding='10' class='style1'>
    <thead>
      <tr><th colspan='8'>Process LIMS Instance Requests</th></tr>
    </thead>
    <tfoot>
      <tr><td colspan='2'><input type='submit' name='edit' value='Edit' />
                          <input type='submit' name='create' value='Create Instance' />
                          <input type='hidden' name='metadataID' value='$metadataID' />
          </td></tr>
    </tfoot>
    <tbody>
      <tr><th colspan='2'>Information about the Database</th></tr>
      <tr><th>Institution:</th>
          <td>$institution</td></tr>
      <tr><th>Database Name:</th>
          <td>$dbname</td></tr>
      <tr><th>The LIMS Username:</th>
          <td>$dbuser</td></tr>
      <tr><th>The LIMS Password:</th>
          <td>$dbpasswd</td></tr>
      <tr><th>The LIMS Server Name:</th>
          <td>$dbhost</td></tr>
      <tr><th colspan='2'>Information about the Administrator</th></tr>
      <tr><th>First Name:</th>
          <td>$admin_fname</td></tr>
      <tr><th>Last Name:</th>
          <td>$admin_lname</td></tr>
      <tr><th>Email:</th>
          <td>$admin_email</td></tr>
      <tr><th colspan='2'>Information about the Lab</th></tr>
      <tr><th>The name of the lab:</th>
          <td>$lab_name</td></tr>
      <tr><th>The building where the lab is located:</th>
          <td>$lab_building</td></tr>
      <tr><th>The room where the lab is located:</th>
          <td>$lab_room</td></tr>
      <tr><th colspan='2'>Information about the AUC Instrument</th></tr>
      <tr><th>The name of the AUC Instrument:</th>
          <td>$instrument_name</td></tr>
      <tr><th>The Instrument Serial #:</th>
          <td>$instrument_serial</td></tr>
      <tr><th colspan='2'>Information about the Instrument Operator</th></tr>
      <tr><th>First Name:</th>
          <td>$operator_fname</td></tr>
      <tr><th>Last Name:</th>
          <td>$operator_lname</td></tr>
      <tr><th>Email:</th>
          <td>$operator_email</td></tr>
      <tr><th colspan='2'>Information about the Channel</th></tr>
      <tr><th>Status:</th>
          <td>$status</td></tr>
    </tbody>
  </table>
  </form>

HTML;
}

// Function to figure out which record to display
function get_id()
{
  // See if we are being directed to a particular record
  if (isset($_GET['ID']))
    return( $_GET['ID'] );

  // We don't know which record, so just find the first one
  $query  = "SELECT metadataID FROM metadata " .
            "ORDER BY updateTime DESC " .
            "LIMIT 1 ";
  $result = mysql_query($query)
      or die("Query failed : $query<br />\n" . mysql_error());

  if (mysql_num_rows($result) == 1)
  {
    list($metadataID) = mysql_fetch_array($result);
    return( $metadataID );
  }

  // If we're here, there aren't any records
echo<<<HTML
  <form action='{$_SERVER[PHP_SELF]}' method='post'>
  <table cellspacing='0' cellpadding='10' class='style1'>
    <thead>
      <tr><th colspan='2'>Process LIMS Instance Requests</th></tr>
    </thead>
    <tfoot>
      <tr><td colspan='2'><input type='submit' name='new' value='New' />
          </td></tr>
    </tfoot>
    <tbody>
      <tr><th>Status:</th>
          <td>There are no records to display</td></tr>
    </tbody>
  </table>
  </form>

HTML;

  return( false );
}

// Function to edit a record
function edit_record()
{
  // Get the record we need to edit
  if ( isset( $_POST['edit'] ) )
    $metadataID = $_POST['metadataID'];

  else if ( isset( $_GET['edit'] ) )
    $metadataID = $_GET['edit'];

  else
  {
    // How did we get here?
    echo "<p>There was a problem with the edit request.</p>\n";
    return;
  }

  $query  = "SELECT institution, dbname, dbuser, dbpasswd, dbhost, " .
            "admin_fname, admin_lname, admin_email, " .
            "lab_name, lab_building, lab_room, " .
            "instrument_name, instrument_serial, " .
            "operator_fname, operator_lname, operator_email, " .
            "status " .
            "FROM metadata " .
            "WHERE metadataID = $metadataID ";
  $result = mysql_query($query) 
            or die("Query failed : $query<br />\n" . mysql_error());

  $row = mysql_fetch_array($result);


  $institution         = html_entity_decode( stripslashes( $row['institution'] ) );
  $dbname              = html_entity_decode( stripslashes( $row['dbname'] ) );
  $dbuser              = html_entity_decode( stripslashes( $row['dbuser'] ) );
  $dbpasswd            = html_entity_decode( stripslashes( $row['dbpasswd'] ) );
  $dbhost              = html_entity_decode( stripslashes( $row['dbhost'] ) );
  $admin_fname         = html_entity_decode( stripslashes( $row['admin_fname'] ) );
  $admin_lname         = html_entity_decode( stripslashes( $row['admin_lname'] ) );
  $admin_email         = html_entity_decode( stripslashes( $row['admin_email'] ) );
  $lab_name            = html_entity_decode( stripslashes( $row['lab_name'] ) );
  $lab_building        = html_entity_decode( stripslashes( $row['lab_building'] ) );
  $lab_room            = html_entity_decode( stripslashes( $row['lab_room'] ) );
  $instrument_name     = html_entity_decode( stripslashes( $row['instrument_name'] ) );
  $instrument_serial   = html_entity_decode( stripslashes( $row['instrument_serial'] ) );
  $operator_fname      = html_entity_decode( stripslashes( $row['operator_fname'] ) );
  $operator_lname      = html_entity_decode( stripslashes( $row['operator_lname'] ) );
  $operator_email      = html_entity_decode( stripslashes( $row['operator_email'] ) );
  $status              =                                   $row['status'];


  $status      = $row['status'];
  $status_text = request_status_select( 'status', $status );

echo<<<HTML
  <form action="{$_SERVER['PHP_SELF']}" method="post">
  <table cellspacing='0' cellpadding='10' class='style1'>
    <thead>
      <tr><th colspan='8'>Process LIMS Instance Requests</th></tr>
    </thead>
    <tfoot>
      <tr><td colspan='2'><input type='submit' name='update' value='Update' />
                          <input type='hidden' name='metadataID' value='$metadataID' />
                          <input type='reset' /></td></tr>
    </tfoot>
    <tbody>

    <tr><th colspan='2'>Information about the Database</th></tr>
    <tr><th>Institution:</th>
        <td><input type='text' name='institution' size='40'
                   maxlength='30' value='$institution' /></td></tr>
    <tr><th>Database Name:</th>
        <td><input type='text' name='dbname' size='40'
                   maxlength='30' value='$dbname' /></td></tr>
    <tr><th>The LIMS Username:</th>
        <td><input type='text' name='dbuser' size='40'
                   maxlength='30' value='$dbuser' /></td></tr>
    <tr><th>The LIMS Password:</th>
        <td><input type='text' name='dbpasswd' size='40'
                   maxlength='30' value='$dbpasswd' /></td></tr>
    <tr><th>The LIMS Server Name:</th>
        <td><input type='text' name='dbhost' size='40'
                   maxlength='30' value='$dbhost' /></td></tr>
    <tr><th colspan='2'>Information about the Administrator</th></tr>
    <tr><th>First Name:</th>
        <td><input type='text' name='admin_fname' size='40'
                   maxlength='30' value='$admin_fname' /></td></tr>
    <tr><th>Last Name:</th>
        <td><input type='text' name='admin_lname' size='40'
                   maxlength='30' value='$admin_lname' /></td></tr>
    <tr><th>Email:</th>
        <td><input type='text' name='admin_email' size='40'
                   maxlength='63' value='$admin_email' /></td></tr>
    <tr><th>Password (for no change leave blank):</th>
        <td><input type='password' name='admin_pw1' size='40'
                   maxlength='80' /></td></tr>
    <tr><th>Password again (must match):</th>
        <td><input type='password' name='admin_pw2' size='40'
                   maxlength='80' /></td></tr>
    <tr><th colspan='2'>Information about the Lab</th></tr>
    <tr><th>The name of the lab:</th>
        <td><textarea name='lab_name' rows='6' cols='65' 
                      wrap='virtual'>$lab_name</textarea></td></tr>
    <tr><th>The building where the lab is located:</th>
        <td><textarea name='lab_building' rows='6' cols='65' 
                      wrap='virtual'>$lab_building</textarea></td></tr>
    <tr><th>The room where the lab is located:</th>
        <td><textarea name='lab_room' rows='6' cols='65' 
                      wrap='virtual'>$lab_room</textarea></td></tr>
    <tr><th colspan='2'>Information about the AUC Instrument</th></tr>
    <tr><th>The name of the AUC Instrument:</th>
        <td><textarea name='instrument_name' rows='6' cols='65' 
                      wrap='virtual'>$instrument_name</textarea></td></tr>
    <tr><th>The Instrument Serial #:</th>
        <td><textarea name='instrument_serial' rows='6' cols='65' 
                      wrap='virtual'>$instrument_serial</textarea></td></tr>
    <tr><th colspan='2'>Information about the Instrument Operator</th></tr>
    <tr><th>First Name:</th>
        <td><input type='text' name='operator_fname' size='40'
                   maxlength='30' value='$operator_fname' /></td></tr>
    <tr><th>Last Name:</th>
        <td><input type='text' name='operator_lname' size='40'
                   maxlength='30' value='$operator_lname' /></td></tr>
    <tr><th>Email:</th>
        <td><input type='text' name='operator_email' size='40'
                   maxlength='63' value='$operator_email' /></td></tr>
    <tr><th>Password (for no change leave blank):</th>
        <td><input type='password' name='operator_pw1' size='40'
                   maxlength='80' /></td></tr>
    <tr><th>Password again (must match):</th>
        <td><input type='password' name='operator_pw2' size='40'
                   maxlength='80' /></td></tr>
    <tr><th colspan='2'>Information about the Channel</th></tr>
    <tr><th>Status:</th>
        <td>$status_text</td></tr>


    </tbody>
  </table>
  </form>

HTML;
}

?>
