<?php
/*
 * global_menu.php
 *
 * Writes a different global menu depending on userlevel, or if logged in
 *
 * Requires session to be started already
 */

$userlevel = ( isset( $_SESSION['userlevel'] ) ) ? $_SESSION['userlevel'] : -1;

if ( $userlevel == 5 ) // super admin
{
$global_menu = <<<HTML
        <div id="globalnav">
          <ul class='level1'>
            <li><a href="http://$org_site/index.php">Home</a></li>
            <li><a href="request_new_instance.php">Request New Instance</a></li>
            <li><a href="view_metadata.php">View Instance Requests</a></li>
            <li><a href="#" onclick='construction();'>Contact Us</a></li>
            <li><a href='#' onclick='construction();'>Help</a></li>
          </ul>
        </div>

HTML;
}

else if ( $userlevel == 4 ) // admin
{
$global_menu = <<<HTML
        <div id="globalnav">
          <ul class='level1'>
            <li><a href="http://$org_site/index.php">Home</a></li>
            <li><a href="request_new_instance.php">Request New Instance</a></li>
            <li><a href="view_metadata.php">View Instance Requests</a></li>
            <li><a href="#" onclick='construction();'>Contact Us</a></li>
            <li><a href='#' onclick='construction();'>Help</a></li>
          </ul>
        </div>

HTML;
}

else if ( $userlevel == 3 ) // super user
{
$global_menu = <<<HTML
        <div id="globalnav">
          <ul class='level1'>
            <li><a href="http://$org_site/index.php">Home</a></li>
            <li><a href="request_new_instance.php">Request New Instance</a></li>
            <li><a href="#" onclick='construction();'>Contact Us</a></li>
            <li><a href='#' onclick='construction();'>Help</a></li>
          </ul>
        </div>

HTML;
}

else if ( $userlevel == 2 ) // analyst
{
$global_menu = <<<HTML
        <div id="globalnav">
          <ul class='level1'>
            <li><a href="http://$org_site/index.php">Home</a></li>
            <li><a href="request_new_instance.php">Request New Instance</a></li>
            <li><a href="#" onclick='construction();'>Contact Us</a></li>
            <li><a href='#' onclick='construction();'>Help</a></li>
          </ul>
        </div>

HTML;
}

else if ( $userlevel == 1 ) // privileged user
{
$global_menu = <<<HTML
        <div id="globalnav">
          <ul class='level1'>
            <li><a href="http://$org_site/index.php">Home</a></li>
            <li><a href="request_new_instance.php">Request New Instance</a></li>
            <li><a href="#" onclick='construction();'>Contact Us</a></li>
            <li><a href='#' onclick='construction();'>Help</a></li>
          </ul>
        </div>

HTML;
}

else if ( $userlevel == 0 ) // regular user
{
$global_menu = <<<HTML
        <div id="globalnav">
          <ul class='level1'>
            <li><a href="http://$org_site/index.php">Home</a></li>
            <li><a href="request_new_instance.php">Request New Instance</a></li>
            <li><a href="#" onclick='construction();'>Contact Us</a></li>
            <li><a href='#' onclick='construction();'>Help</a></li>
          </ul>
        </div>

HTML;
}

else // not logged in, userlevel not 0-5 for some reason, etc.
{
$global_menu = <<<HTML
        <div id="globalnav">
          <ul class='level1'>
            <li><a href="http://$org_site/index.php">Home</a></li>
            <li><a href="request_new_instance.php">Request New Instance</a></li>
            <li><a href="#" onclick='construction();'>Contact Us</a></li>
          </ul>
        </div>

HTML;
}

