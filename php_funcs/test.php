<?php

include "database.php";

echo getcwd();

execInBackground("php background.php");
execInBackground("php ./background.php");
echo("Running");
