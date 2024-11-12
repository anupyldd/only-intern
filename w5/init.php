<?php

AddEventHandler("dev.site", "OnAfterIBlockElementAdd", ["Iblock", "clearOldLogs"]);
AddEventHandler("dev.site", "OnAfterIBlockElementUpdate", ["Iblock", "clearOldLogs"]);
