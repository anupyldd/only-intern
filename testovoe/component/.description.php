<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = [
    "NAME" => "Список доступных автомобилей",
    "DESCRIPTION" => "Компонент для отображения списка свободных автомобилей для сотрудника.",
    "SORT" => 100, 
    "CACHE_PATH" => "Y",
    "PATH" => [
        "ID" => "custom_components", 
        "NAME" => "Пользовательские компоненты", 
        "CHILD" => [
            "ID" => "cars_components", 
            "NAME" => "Компоненты автомобилей",
        ],
    ],
];