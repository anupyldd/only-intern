<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arParams['IBLOCK_TYPE'] = isset($arParams['IBLOCK_TYPE']) ? trim($arParams['IBLOCK_TYPE']) : '';

$arParams['IBLOCK_ID'] = !empty($arParams['IBLOCK_ID']) ? (int)$arParams['IBLOCK_ID'] : null;

if (isset($arParams['FILTER'])) {
    if (is_string($arParams['FILTER'])) {
        $decodedFilter = json_decode($arParams['FILTER'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            ShowError('Invalid FILTER parameter');
            $arParams['FILTER'] = [];
        } else {
            $arParams['FILTER'] = $decodedFilter;
        }
    } elseif (!is_array($arParams['FILTER'])) {
        $arParams['FILTER'] = [];
    }
} else {
    $arParams['FILTER'] = [];
}

$component = new NewsListCustom();
$component->arParams = $arParams;
$component->executeComponent();
