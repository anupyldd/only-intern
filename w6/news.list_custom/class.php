<?php

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class NewsListCustom extends CBitrixComponent
{
    protected function validateParams()
    {
        if (!Loader::includeModule('iblock')) {
            ShowError("Failed to include Iblock module");
            return false;
        }

        if (empty($this->arParams['IBLOCK_TYPE']) && empty($this->arParams['IBLOCK_ID'])) {
            ShowError("'IBLOCK_TYPE' or 'IBLOCK_ID' parameter is necessary.");
            return false;
        }        

        if (isset($this->arParams['IBLOCK_ID']) && !is_numeric($this->arParams['IBLOCK_ID'])) {
            ShowError("'IBLOCK_ID' parameter has to be numeric.");
            return false;
        }

        return true;
    }

    protected function getItems()
    {
        $filter = [
            'ACTIVE' => 'Y',
        ];

        if (!empty($this->arParams['IBLOCK_TYPE'])) {
            $iblocks = [];
            $iblockResult = \CIBlock::GetList(
                [], 
                ['TYPE' => $this->arParams['IBLOCK_TYPE'], 'ACTIVE' => 'Y']
            );
            while ($iblock = $iblockResult->Fetch()) {
                $iblocks[] = $iblock['ID'];
            }

            if (empty($iblocks)) {
                return [];
            }

            $filter['IBLOCK_ID'] = $iblocks;
        }

        if (!empty($this->arParams['IBLOCK_ID'])) {
            $filter['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
        }

        if (!empty($this->arParams['FILTER']) && is_array($this->arParams['FILTER'])) {
            $filter = array_merge($filter, $this->arParams['FILTER']);
        }

        $select = [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'PREVIEW_TEXT',
            'DETAIL_PAGE_URL',
        ];

        $items = [];
        $result = ElementTable::getList([
            'filter' => $filter,
            'select' => $select,
            'order' => ['IBLOCK_ID' => 'ASC', 'ID' => 'ASC'],
        ]);

        while ($item = $result->fetch()) {
            $items[$item['IBLOCK_ID']][] = $item;
        }

        if (empty($items)) {
            ShowError("No elements found for the given parameters.");
        }        

        return $items;
    }

    public function executeComponent()
    {
        if (!$this->validateParams()) {
            return;
        }

        $this->arResult['ITEMS'] = $this->getItems();
        $this->includeComponentTemplate();
    }
}
