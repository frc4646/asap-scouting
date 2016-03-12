<?php

/**
 * FRC Scouting Application (Written for FRC Team 4646 ASAP)
 *
 * @author      Alexander Young <meun5@team4646.org>
 * @copyright   2016 Alexander Young
 * @link        https://github.com/meun5/asap-scouting
 * @license     https://github.com/meun5/asap-scouting/blob/master/LICENSE
 * @version     v1-beta
 */

if (file_exists("../.maintaince")) {
	header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
	header("Pragma: no-cache");
	echo file_get_contents("../asap-scouting/views/templates/maintaince.twig");
	die();
}

require_once("../asap-scouting/start.php");

$app->run();
