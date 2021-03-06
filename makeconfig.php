<?php
/*
 * makeconfig.php
 *
 * Creates a config.php file
 *
 */
session_start();

/*
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
*/

include 'config.php';
include 'db.php';

// Make sure there is a parameter
if ( $_SERVER['argc'] != 2 )
{
  echo "Usage: php makeconfig.php <db_name>";
  exit();
}

$new_dbname = $_SERVER['argv'][1];

$query  = "SELECT institution, dbuser, dbpasswd, dbhost, " .
          "secure_user, secure_pw, " .
          "admin_fname, admin_lname, admin_email, admin_pw " .
          "FROM metadata " .
          "WHERE dbname = '$new_dbname' ";

$result = mysql_query($query) 
          or die("Query failed : $query<br />\n" . mysql_error());

if ( mysql_num_rows( $result ) != 1 )
{
  echo "$new_dbname not found\n";
  exit();
}

list( $institution,
      $new_dbuser,
      $new_dbpasswd,
      $new_dbhost,
      $secure_user,
      $secure_pw,
      $admin_fname,
      $admin_lname,
      $admin_email,
      $admin_pw )   = mysql_fetch_array( $result );

$today  = date("Y\/m\/d");
$year   = date( "Y" );

$text = <<<TEXT
<?php
/*  Database and other configuration information - Required!!  
 -- Configure the Variables Below --

*/

\$org_name           = 'UltraScan3 LIMS portal';
\$org_site           = 'uslims3.uthscsa.edu/$new_dbname';
\$site_author        = 'Dan Zollars, University of Texas Health Science Center';
\$site_keywords      = 'ultrascan analytical ultracentrifugation lims';
                      # The website keywords (meta tag)
\$site_desc          = 'Website for the UltraScan3 LIMS portal'; # Site description

\$admin              = '$admin_fname $admin_lname';
\$admin_phone        = 'Office: <br />Fax: ';
\$admin_email        = '$admin_email';

\$dbusername         = '$new_dbuser';  # the name of the MySQL user
\$dbpasswd           = '$new_dbpasswd';  # the password for the MySQL user
\$dbname             = '$new_dbname';  # the name of the database
\$dbhost             = '$new_dbhost'; # the host on which MySQL runs, generally localhost

// Secure user credentials
\$secure_user        = '$secure_user'; # the secure username that UltraScan3 uses
\$secure_pw          = '$secure_pw';   # the secure password that UltraScan3 uses

// Global DB
\$globaldbuser       = 'gfac';  # the name of the MySQL user
\$globaldbpasswd     = 'backend';  # the password for the MySQL user
\$globaldbname       = 'gfac';  # the name of the database
\$globaldbhost       = 'uslims3.uthscsa.edu'; # the host on which MySQL runs, generally localhost

// Admin function
\$v1_host            = "";
\$v1_user            = "";
\$v1_pass            = "";

\$v2_host            = "";
\$v2_user            = "";
\$v2_pass            = "";

\$ipaddr             = '129.111.140.156'; # the external IP address of the host machine
\$udpport            = 12233; # the port to send udp messages to

\$top_image          = '#';  # name of the logo to use
\$top_banner         = 'images/#';  # name of the banner at the top

\$full_path          = '$dest_path$new_dbname/';  # Location of the system code
\$data_dir           = '$dest_path$new_dbname/data/'; # Full path
\$submit_dir         = '/srv/www/htdocs/uslims3/uslims3_data/'; # Full path
\$class_dir          = '/srv/www/htdocs/common/class/'; #Full path
\$disclaimer_file    = ''; # the name of a text file with disclaimer info

// Dates
date_default_timezone_set( 'America/Chicago' );
\$last_update        = '$today'; # the date the website was last updated
\$copyright_date     = '$year'; # copyright date
\$current_year       = date( 'Y' );

//////////// End of user specific configuration

// ensure a trailing slash
if ( \$data_dir[strlen(\$data_dir) - 1] != '/' )
  \$data_dir .= '/';

if ( \$submit_dir[strlen(\$submit_dir) - 1] != '/' )
  \$submit_dir .= '/';

if ( \$class_dir[strlen(\$class_dir) - 1] != '/' )
  \$class_dir .= '/';

/* Define our file paths */
if ( ! defined('HOME_DIR') ) 
{
  define('HOME_DIR', \$full_path );
}

if ( ! defined('DEBUG') ) 
{
  define('DEBUG', false );
}

?>
TEXT;

if ( file_exists( $dest_path . $new_dbname ) )
  file_put_contents( $dest_path . "$new_dbname/config.php", $text );

else
{
  global $data_dir;

  file_put_contents( $data_dir . 'config.php', $text );
}

?>
