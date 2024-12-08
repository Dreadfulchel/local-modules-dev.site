<?php

use Bitrix\Main\Loader;
use Only\Site\Handlers\Iblock as IblockHandlers;
use Only\Site\Agents\Iblock as IblockAgents;

Loader::includeModule('dev.site');

AddEventHandler("iblock", "OnBeforeIBlockElementAdd", [IblockHandlers::class, "OnBeforeIBlockElementAddHandler"]);

\CAgent::AddAgent(
    "\\Only\\Site\\Agents\\Iblock::clearOldLogs();",
    "", 
    "N", 
    86400 
);
