<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/IBlockLogger.php";

AddEventHandler("iblock", "OnAfterIBlockElementAdd", ["IBlockLogger", "logElementChanges"]);
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", ["IBlockLogger", "logElementChanges"]);
