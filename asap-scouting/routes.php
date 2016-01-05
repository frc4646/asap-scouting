<?php

/**
 * FRC Scouting Application (Written for FRC Team 4646 ASAP )
 *
 * @author      Alexander Young <meun5@team4646.org>
 * @copyright   2015 Alexander Young
 * @link        https://github.com/meun5/asap-scouting
 * @license     https://github.com/meun5/asap-scouting/blob/master/LICENSE
 * @version     0.3.0
 */

//Home Route
require INC_ROOT . "/asap-scouting/routes/home.php";

//Authentication Routes
require INC_ROOT . "/asap-scouting/routes/auth/login.php";
require INC_ROOT . "/asap-scouting/routes/auth/logout.php";
require INC_ROOT . "/asap-scouting/routes/auth/register.php";
