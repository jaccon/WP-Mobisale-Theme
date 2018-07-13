<?php
$directory = dirname ( __FILE__ )."/scss";

require "scss.inc.php";
scss_server::serveFrom($directory);
