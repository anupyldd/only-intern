<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    'GROUPS' => [],
    'PARAMETERS' => [
        'IBLOCK_TYPE' => [
            'PARENT' => 'BASE',
            'NAME' => 'Тип инфоблока',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'IBLOCK_ID' => [
            'PARENT' => 'BASE',
            'NAME' => 'ID инфоблока',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'FILTER' => [
            'PARENT' => 'DATA_SOURCE',
            'NAME' => 'Фильтр',
            'TYPE' => 'STRING',
            'MULTIPLE' => 'Y',
            'DEFAULT' => [],
        ],
    ],
];
