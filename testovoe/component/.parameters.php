<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = [
    "PARAMETERS" => [
        "HIGHLOADBLOCK_CARS_ID" => [
            "PARENT" => "BASE",
            "NAME" => "ID хайлоадблока автомобилей",
            "TYPE" => "STRING",
            "DEFAULT" => "1",
        ],
        "HIGHLOADBLOCK_CATEGORIES_ID" => [
            "PARENT" => "BASE",
            "NAME" => "ID хайлоадблока категорий комфорта",
            "TYPE" => "STRING",
            "DEFAULT" => "2",
        ],
        "HIGHLOADBLOCK_POSITIONS_ID" => [
            "PARENT" => "BASE",
            "NAME" => "ID хайлоадблока должностей",
            "TYPE" => "STRING",
            "DEFAULT" => "3",
        ],
        "CACHE_TIME" => [
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => "Время кэширования (секунды)",
            "TYPE" => "STRING",
            "DEFAULT" => "3600",
        ],
    ],
];