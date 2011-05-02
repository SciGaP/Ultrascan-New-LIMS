<?php
/*
 * get_meta_info.php
 *
 * Include file to get and process some common user information
 *  This is used in two files --- request_new_instance.php
 *  and edit_metadata.php
 *
 */


$institution       = trim(substr(addslashes(htmlentities($_POST['institution'])), 0,45));
$admin_fname       = trim(substr(addslashes(htmlentities($_POST['admin_fname'])), 0,30));
$admin_lname       = trim(substr(addslashes(htmlentities($_POST['admin_lname'])), 0,30));
$admin_email       = trim(substr(addslashes(htmlentities($_POST['admin_email'])), 0,63));
$lab_name          = trim(       addslashes(htmlentities($_POST['lab_name'])));
$lab_building      = trim(       addslashes(htmlentities($_POST['lab_building'])));
$lab_room          = trim(       addslashes(htmlentities($_POST['lab_room'])));
$instrument_name   = trim(       addslashes(htmlentities($_POST['instrument_name'])));
$instrument_serial = trim(       addslashes(htmlentities($_POST['instrument_serial'])));
$operator_fname    = trim(substr(addslashes(htmlentities($_POST['operator_fname'])), 0,30));
$operator_lname    = trim(substr(addslashes(htmlentities($_POST['operator_lname'])), 0,30));
$operator_email    = trim(substr(addslashes(htmlentities($_POST['operator_email'])), 0,63));

// Let's do some error checking first of all
// -- most fields are required
$message = "";
if ( empty($institution) )
  $message .= "--institution is missing<br />";

if ( empty($admin_fname) )
  $message .= "--administrator first name is missing<br />";

if ( empty($admin_lname) )
  $message .= "--administrator last name is missing<br />";

if ( empty($admin_email) )
  $message .= "--administrator email address is missing<br />";

if (! emailsyntax_is_valid($admin_email) )
{
  $message .= "--administrator email is not a valid email address<br />";
  $admin_email = '';
}

if ( empty($lab_name) )
  $message .= "--lab name is missing<br />";

if ( empty($lab_building) )
  $message .= "--lab building is missing<br />";

if ( empty($lab_room) )
  $message .= "--lab room is missing<br />";

if ( empty($instrument_name) )
  $message .= "--instrument name is missing<br />";

if ( empty($instrument_serial) )
  $message .= "--instrument serial number is missing<br />";

if ( empty($operator_fname) )
  $message .= "--operator first name is missing<br />";

if ( empty($operator_lname) )
  $message .= "--operator last name is missing<br />";

if ( empty($operator_email) )
  $message .= "--operator email address is missing<br />";

if (! emailsyntax_is_valid($operator_email) )
{
  $message .= "--operator email is not a valid email address<br />";
  $operator_email = '';
}
?>
