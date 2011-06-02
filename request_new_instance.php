<?php
/*
 * request_new_instance.php
 *
 * A place to request a new LIMS instance
 *
 */
session_start();

include 'config.php';
include 'db.php';

// Start displaying page
$page_title = 'Request a New LIMS Instance';
//$js = 'js/request_new_instance.js';
include 'top.php';
include 'links.php';
include 'lib/utility.php';

// Let's keep track of these fields
$fields = array( 'institution',
                 'inst_abbrev',
                 'admin_fname',
                 'admin_lname',
                 'admin_email',
                 'lab_name',
                 'lab_contact',
                 'instrument_name',
                 'instrument_serial' );
foreach ( $fields as $field )
  if ( ! isset( $$field ) )
    $$field = '';

?>
<!-- Begin page content -->
<div id='content'>

  <h1 class="title">Request a New LIMS Instance</h1>
  <!-- Place page content here -->

<?php

// Are we being directed to save the data?
if ( isset( $_POST['create'] ) )
  do_create();

// No, so are we being directed to enter the data?
else if ( isset( $_POST['enter_request'] ) )
{
  // Check if they match
  if ( $_POST['captcha'] == $_SESSION['captcha'] )
    enter_record();

  else
    do_captcha( "Entered text doesn&rsquo;t match." );
}

else                  // No, just display the captcha
  do_captcha();

?>
</div>

<?php
include 'bottom.php';
exit();

// Function to create the new record
function do_create()
{
  include 'get_meta_info.php';
  $admin_pw1    = trim(substr(addslashes(htmlentities($_POST['admin_pw1'])), 0,80));
  $admin_pw2    = trim(substr(addslashes(htmlentities($_POST['admin_pw2'])), 0,80));

  if ( empty( $admin_pw1 ) )
    $message .= "--administrator password cannot be empty.<br />";

  if ( $admin_pw1 != $admin_pw2 )
    $message .= "--administrator passwords do not match.<br />";

  // Check $inst_abbrev, which needs to be unique
  $inst_abbrev = preg_replace( "/ /", "_", $inst_abbrev );
  $query  = "SELECT COUNT(*) FROM metadata " .
            "WHERE inst_abbrev = '$inst_abbrev' ";
  $result =  mysql_query($query)
             or die("Query failed : $query<br />\n" . mysql_error());
  list( $count ) = mysql_fetch_array( $result );
  if ( $count > 0 )
    $message .= "--abbreviation $inst_abbrev is already in use.<br />";

  // Keep track of these in session variables
  global $fields;
  foreach( $fields as $field )
    $_SESSION[ $field ] = $$field;

  // Default php usernames and such
  $dbname   = $inst_abbrev ;   // 10 chars max
  $dbuser   = $dbname . '_user';
  $dbname   = "uslims3_$dbname";
  $dbpasswd = makeRandomPassword();
  $dbhost   = 'ultrascan3.uthscsa.edu';

  if ( empty( $message ) )
  {
    $query = "INSERT INTO metadata " .
             "SET institution  = '$institution', " .
             "inst_abbrev = '$inst_abbrev', " .
             "dbname = '$dbname', " .
             "dbuser = '$dbuser', " .
             "dbpasswd = '$dbpasswd', " .
             "dbhost = '$dbhost', " .
             "admin_fname  = '$admin_fname', " .
             "admin_lname  = '$admin_lname', " .
             "admin_email  = '$admin_email', " .
             "admin_pw  = '$admin_pw1', " .
             "lab_name  = '$lab_name', " .
             "lab_contact  = '$lab_contact', " .
             "instrument_name  = '$instrument_name', " .
             "instrument_serial  = '$instrument_serial', " .
             "status = 'pending', " .
             "updateTime = NOW() ";

    mysql_query($query)
      or die("Query failed : $query<br />\n" . mysql_error());

    show_record();
  }

  else
  {
    echo "<p class='message'>The following errors were noted:</p>\n";
    echo "<p class='message'>$message</p>\n";
    enter_record();
  }
}

// Function to show what we just entered
function show_record()
{
  global $fields;
  foreach ( $fields as $field )
  {
    $$field = $_SESSION[ $field ];
    unset( $_SESSION[ $field ] );
  }

echo<<<HTML
  <table cellspacing='0' cellpadding='10' class='style1'>
    <thead>
      <tr><th colspan='8'>New LIMS Instance Information Saved</th></tr>
    </thead>
    <tbody>
      <tr><th colspan='2'>Information about the Institution</th></tr>
      <tr><th>Institution:</th>
          <td>$institution</td></tr>
      <tr><th>Institution Abbreviation:</th>
          <td>$inst_abbrev</td></tr>
      <tr><th>First Name:</th>
          <td>$admin_fname</td></tr>
      <tr><th>Last Name:</th>
          <td>$admin_lname</td></tr>
      <tr><th>Email:</th>
          <td>$admin_email</td></tr>
      <tr><th colspan='2'>Information about the Facility</th></tr>
      <tr><th>The Facility Name:</th>
          <td>$lab_name</td></tr>
      <tr><th>Contact information for the facility:</th>
          <td>$lab_contact</td></tr>
      <tr><th colspan='2'>Information about the AUC Instrument</th></tr>
      <tr><th>The name of the AUC Instrument:</th>
          <td>$instrument_name</td></tr>
      <tr><th>The Instrument Serial #:</th>
          <td>$instrument_serial</td></tr>
    </tbody>
  </table>

HTML;
}

// Function to enter a new record
function enter_record()
{
  global $fields;
  foreach ( $fields as $field )
  {
    $$field = $_SESSION[ $field ];
    unset( $_SESSION[ $field ] );
  }

echo<<<HTML
  <form action="{$_SERVER['PHP_SELF']}" method="post">
  <table cellspacing='0' cellpadding='10' class='style1'>
    <thead>
      <tr><th colspan='8'>Request a New LIMS Instance</th></tr>
    </thead>
    <tfoot>
      <tr><td colspan='2'><input type='submit' name='create' value='Create' />
                          <input type='reset' /></td></tr>
    </tfoot>
    <tbody>

    <tr><th colspan='2'>Information about the Institution</th></tr>
    <tr><th>Name of the Institution:</th>
        <td><input type='text' name='institution' size='40'
                   maxlength='45' value='$institution' /></td></tr>
    <tr><th>Short Abbreviation for the Institution (10 chars max):</th>
        <td><input type='text' name='inst_abbrev' size='40'
                   maxlength='10' value='$inst_abbrev' /></td></tr>
    <tr><th colspan='2'>Information about the Facility Administrator</th></tr>
    <tr><th>First Name:</th>
        <td><input type='text' name='admin_fname' size='40'
                   maxlength='30' value='$admin_fname' /></td></tr>
    <tr><th>Last Name:</th>
        <td><input type='text' name='admin_lname' size='40'
                   maxlength='30' value='$admin_lname' /></td></tr>
    <tr><th>Email:</th>
        <td><input type='text' name='admin_email' size='40'
                   maxlength='63' value='$admin_email' /></td></tr>
    <tr><th>Administrator&rsquo;s LIMS Password:</th>
        <td><input type='password' name='admin_pw1' size='40'
                   maxlength='80' /></td></tr>
    <tr><th>LIMS Password again (must match):</th>
        <td><input type='password' name='admin_pw2' size='40'
                   maxlength='80' /></td></tr>
    <tr><th colspan='2'>Information about the Facility</th></tr>
    <tr><th>The Facility Name:</th>
        <td><input type='text' name='lab_name' size='40' 
                   maxlength='80' /></td></tr>
    <tr><th>Facility Contact Information:</th>
        <td><textarea name='lab_contact' rows='6' cols='65' 
                      wrap='virtual'>$lab_contact</textarea></td></tr>
    <tr><th colspan='2'>Information about the AUC Instrument</th></tr>
    <tr><th>The name of the AUC Instrument:</th>
        <td><input type='text' name='instrument_name' size='40' 
                   maxlength='80' value='$instrument_name' /></td></tr>
    <tr><th>The Instrument Serial #:</th>
        <td><input type='text' name='instrument_serial' size='40' 
                   maxlength='80' value='$instrument_serial' /></td></tr>
    </tbody>
  </table>
  </form>

HTML;
}

// Function to display a captcha and request human input
function do_captcha( $msg = "" )
{
  $message = ( empty( $msg ) ) ? "" : "<p style='color:red;'>$msg</p>";

  // Let's just use the random password function we already have
  $pw = makeRandomPassword();
  $_SESSION['captcha'] = $pw;

echo<<<HTML
  <div id='captcha'>

  $message

  <img src='create_captcha.php' alt='Captcha image' />

  <form action="{$_SERVER['PHP_SELF']}" method="post">
    <h3>Please enter the code above to proceed to new instance request</h3>

    <p><input type='text' name='captcha' size='40' maxlength='10' /></p>

    <p><input type='submit' name='enter_request' value='Enter Request' />
       <input type='reset' /></p>

  </form>

  </div>

HTML;
}

?>
