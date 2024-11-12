<?php

use Bitrix\Main\Loader;

Loader::includeModule('iblock');

class IBlockLogger 
{
    
    private $logIblockId;

    public function __construct() 
    {
        $this->logIblockId = $this->getLogIBlockId();
    }

    public function logElementChanges(&$arFields) 
    {
        if (!$arFields["ID"] || $arFields["IBLOCK_ID"] == $this->logIblockId) {
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

    public function deleteOldLogs() 
    {
        $logElements = CIBlockElement::GetList(
            ["ACTIVE_FROM" => "DESC"], 
            ["IBLOCK_ID" => $this->logIblockId],
            false,
            ["nPageSize" => 0, "iNumPage" => 2, "nTopCount" => 10], 
            ["ID"]
        );

        while ($log = $logElements->GetNext()) {
            CIBlockElement::Delete($log["ID"]);
        }
    }

    private function getLogIBlockId() 
    {
        $logIblockCode = "LOG";
        $iblock = CIBlock::GetList([], ["CODE" => $logIblockCode])->Fetch();
        return $iblock ? $iblock["ID"] : null;
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

    public static function deleteOldLogsAgent() 
    {
        $logger = new self();
        $logger->deleteOldLogs();
        return "IBlockLogger::deleteOldLogsAgent();";
    }
}

new IBlockLogger();
