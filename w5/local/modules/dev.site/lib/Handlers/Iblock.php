<?php

namespace Only\Site\Handlers;


class Iblock
{
    private $logIblockId;

    public function addLog($arFields)
    {
        $logIblockId = CIBlock::GetList([], ["CODE" => "LOG"])->Fetch();

        if (!$arFields["ID"] || $arFields["IBLOCK_ID"] == $this->$logIblockId) {
            return;
        }

        $elementId = $arFields["ID"];
        $iblockId = $arFields["IBLOCK_ID"];
        $elementData = $this->getElementInfo($iblockId, $elementId);

        if (!$elementData) {
            return;
        }

        $sectionId = $this->ensureSectionExists($elementData['IBLOCK_NAME'], $elementData['IBLOCK_CODE']);
        $logData = [
            "IBLOCK_ID" => $this->logIblockId,
            "NAME" => $elementId,
            "ACTIVE_FROM" => date("d.m.Y H:i:s"),
            "PREVIEW_TEXT" => $elementData['DESCRIPTION'],
            "IBLOCK_SECTION_ID" => $sectionId,
        ];

        $el = new CIBlockElement;
        $el->Add($logData);
    }

    private function getElementInfo($iblockId, $elementId) 
    {
        $element = CIBlockElement::GetByID($elementId)->GetNext();
        if (!$element) {
            return null;
        }

        $iblock = CIBlock::GetByID($iblockId)->GetNext();
        $description = $iblock["NAME"] . " -> " . $this->getFullSectionPath($element["IBLOCK_SECTION_ID"]) . " -> " . $element["NAME"];

        return [
            'IBLOCK_NAME' => $iblock["NAME"],
            'IBLOCK_CODE' => $iblock["CODE"],
            'DESCRIPTION' => $description,
        ];
    }

    private function getFullSectionPath($sectionId) 
    {
        $path = [];
        while ($sectionId) {
            $section = CIBlockSection::GetByID($sectionId)->GetNext();
            if ($section) {
                $path[] = $section["NAME"];
                $sectionId = $section["IBLOCK_SECTION_ID"];
            } else {
                break;
            }
        }
        return implode(" -> ", array_reverse($path));
    }

    private function ensureSectionExists($sectionName, $sectionCode) 
    {
        $section = CIBlockSection::GetList([], [
            "IBLOCK_ID" => $this->logIblockId,
            "NAME" => $sectionName,
            "CODE" => $sectionCode
        ])->Fetch();

        if ($section) {
            return $section["ID"];
        }

        $bs = new CIBlockSection;
        $sectionData = [
            "IBLOCK_ID" => $this->logIblockId,
            "NAME" => $sectionName,
            "CODE" => $sectionCode,
        ];
        return $bs->Add($sectionData);
    }


/************************************************************/
/************************************************************/
/************************************************************/

    function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        $iQuality = 95;
        $iWidth = 1000;
        $iHeight = 1000;
        /*
         * Получаем пользовательские свойства
         */
        $dbIblockProps = \Bitrix\Iblock\PropertyTable::getList(array(
            'select' => array('*'),
            'filter' => array('IBLOCK_ID' => $arFields['IBLOCK_ID'])
        ));
        /*
         * Выбираем только свойства типа ФАЙЛ (F)
         */
        $arUserFields = [];
        while ($arIblockProps = $dbIblockProps->Fetch()) {
            if ($arIblockProps['PROPERTY_TYPE'] == 'F') {
                $arUserFields[] = $arIblockProps['ID'];
            }
        }
        /*
         * Перебираем и масштабируем изображения
         */
        foreach ($arUserFields as $iFieldId) {
            foreach ($arFields['PROPERTY_VALUES'][$iFieldId] as &$file) {
                if (!empty($file['VALUE']['tmp_name'])) {
                    $sTempName = $file['VALUE']['tmp_name'] . '_temp';
                    $res = \CAllFile::ResizeImageFile(
                        $file['VALUE']['tmp_name'],
                        $sTempName,
                        array("width" => $iWidth, "height" => $iHeight),
                        BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
                        false,
                        $iQuality);
                    if ($res) {
                        rename($sTempName, $file['VALUE']['tmp_name']);
                    }
                }
            }
        }

        if ($arFields['CODE'] == 'brochures') {
            $RU_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_RU');
            $EN_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_EN');
            if ($arFields['IBLOCK_ID'] == $RU_IBLOCK_ID || $arFields['IBLOCK_ID'] == $EN_IBLOCK_ID) {
                \CModule::IncludeModule('iblock');
                $arFiles = [];
                foreach ($arFields['PROPERTY_VALUES'] as $id => &$arValues) {
                    $arProp = \CIBlockProperty::GetByID($id, $arFields['IBLOCK_ID'])->Fetch();
                    if ($arProp['PROPERTY_TYPE'] == 'F' && $arProp['CODE'] == 'FILE') {
                        $key_index = 0;
                        while (isset($arValues['n' . $key_index])) {
                            $arFiles[] = $arValues['n' . $key_index++];
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'L' && $arProp['CODE'] == 'OTHER_LANG' && $arValues[0]['VALUE']) {
                        $arValues[0]['VALUE'] = null;
                        if (!empty($arFiles)) {
                            $OTHER_IBLOCK_ID = $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? $EN_IBLOCK_ID : $RU_IBLOCK_ID;
                            $arOtherElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => $OTHER_IBLOCK_ID,
                                    'CODE' => $arFields['CODE']
                                ], false, false, ['ID'])
                                ->Fetch();
                            if ($arOtherElement) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arOtherElement['ID'], $OTHER_IBLOCK_ID, $arFiles, 'FILE');
                            }
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'E') {
                        $elementIds = [];
                        foreach ($arValues as &$arValue) {
                            if ($arValue['VALUE']) {
                                $elementIds[] = $arValue['VALUE'];
                                $arValue['VALUE'] = null;
                            }
                        }
                        if (!empty($arFiles && !empty($elementIds))) {
                            $rsElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => \Only\Site\Helpers\IBlock::getIblockID('PRODUCTS', 'CATALOG_' . $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? '_RU' : '_EN'),
                                    'ID' => $elementIds
                                ], false, false, ['ID', 'IBLOCK_ID', 'NAME']);
                            while ($arElement = $rsElement->Fetch()) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], $arFiles, 'FILE');
                            }
                        }
                    }
                }
            }
        }
    }

}
