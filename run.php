<?php

define("_ROOTDIR_", __DIR__);

require_once( __DIR__ . "/library/std.lib.php" );
require_once( __DIR__ . "/library/NewsFeed.php" );

$feed = new NewsFeed($argv);

/**
 * @TODO
 * - add custom exeption class
 * - resacale downloaded images
 * - write properly autoload
 * - rewrite database class for procedure use only
 * - rewrite scripts to objected oriented
 * - rebuild main class for difrent type of input data (for eg. html)
 */