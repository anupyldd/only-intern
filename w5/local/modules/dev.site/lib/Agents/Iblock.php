<?php

namespace Only\Site\Agents;


class Iblock
{
    public static function clearOldLogs()
    {
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $logElements = CIBlockElement::GetList(
                ["ACTIVE_FROM" => "DESC"], 
                ["IBLOCK_ID" => CIBlock::GetList([], ["CODE" => "LOG"])->Fetch()["ID"]],
                false,
                ["nPageSize" => 0, "iNumPage" => 2, "nTopCount" => 10], 
                ["ID"]
            );

            while ($log = $logElements->GetNext()) {
                CIBlockElement::Delete($log["ID"]);
            }
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';;
    }

    public static function example()
    {
        global $DB;
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockId = \Only\Site\Helpers\IBlock::getIblockID('QUARRIES_SEARCH', 'SYSTEM');
            $format = $DB->DateFormatToPHP(\CLang::GetDateFormat('SHORT'));
            $rsLogs = \CIBlockElement::GetList(['TIMESTAMP_X' => 'ASC'], [
                'IBLOCK_ID' => $iblockId,
                '<TIMESTAMP_X' => date($format, strtotime('-1 months')),
            ], false, false, ['ID', 'IBLOCK_ID']);
            while ($arLog = $rsLogs->Fetch()) {
                \CIBlockElement::Delete($arLog['ID']);
            }
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}
