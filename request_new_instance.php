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
                 'admin_fname',
                 'admin_lname',
                 'admin_email',
                 'lab_name',
                 'lab_building',
                 'lab_room',
                 'instrument_name',
                 'instrument_serial',
                 'operator_fname',
                 'operator_lname',
                 'operator_email' );
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

else                  // No, just enter the data
  enter_record();

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
  $operator_pw1 = trim(substr(addslashes(htmlentities($_POST['operator_pw1'])), 0,80));
  $operator_pw2 = trim(substr(addslashes(htmlentities($_POST['operator_pw2'])), 0,80));

  if ( empty( $admin_pw1 ) )
    $message .= "--administrator password cannot be empty.<br />";

  if ( $admin_pw1 != $admin_pw2 )
    $message .= "--administrator passwords do not match.<br />";

  if ( empty( $operator_pw1 ) )
    $message .= "--operator password cannot be empty.<br />";

  if ( $operator_pw1 != $operator_pw2 )
    $message .= "--operator passwords do not match.<br />";

  // Keep track of these in session variables
  global $fields;
  foreach( $fields as $field )
    $_SESSION[ $field ] = $$field;

  if ( empty( $message ) )
  {
    $query = "INSERT INTO metadata " .
             "SET institution  = '$institution', " .
             "admin_fname  = '$admin_fname', " .
             "admin_lname  = '$admin_lname', " .
             "admin_email  = '$admin_email', " .
             "admin_pw  = MD5('$admin_pw1'), " .
             "lab_name  = '$lab_name', " .
             "lab_building  = '$lab_building', " .
             "lab_room  = '$lab_room', " .
             "instrument_name  = '$instrument_name', " .
             "instrument_serial  = '$instrument_serial', " .
             "operator_fname  = '$operator_fname', " .
             "operator_lname  = '$operator_lname', " .
             "operator_email  = '$operator_email', " .
             "operator_pw  = MD5('$operator_pw1'), " .
             "status = 'requested', " .
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
    <tr><th>Institution:</th>
        <td><input type='text' name='institution' size='40'
                   maxlength='45' value='$institution' /></td></tr>
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
    <tr><th colspan='2'>Information about the Lab</th></tr>
    <tr><th>The facility name:</th>
        <td><input type='text' name='lab_name' size='40' 
                   maxlength='80' /></td></tr>
    <!--tr><th>The building where the lab is located:</th>
        <td><input type='text' name='lab_building' size='40' 
                   maxlength='80' value='$lab_building' /></td></tr>
    <tr><th>The room where the lab is located:</th>
        <td><textarea name='lab_room' rows='6' cols='65' 
                      wrap='virtual'>$lab_room</textarea></td></tr-->
<!-- Add here a box with textarea for contact info -->
    <tr><th colspan='2'>Information about the AUC Instrument</th></tr>
    <tr><th>The name of the AUC Instrument:</th>
        <td><input type='text' name='instrument_name' size='40' 
                   maxlength='80' value='$instrument_name' /></td></tr>
    <tr><th>The Instrument Serial #:</th>
        <td><input type='text' name='instrument_serial' size='40' 
                   maxlength='80' value='$instrument_serial' /></td></tr>
<!-- replace operator with the administrator above
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
    <tr><th>Password:</th>
        <td><input type='password' name='operator_pw1' size='40'
                   maxlength='80' /></td></tr>
    <tr><th>Password again (must match):</th>
        <td><input type='password' name='operator_pw2' size='40'
                   maxlength='80' /></td></tr>
-->
    </tbody>
  </table>
  </form>

HTML;
}

?>
