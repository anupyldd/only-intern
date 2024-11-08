<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
if (!$USER->IsAdmin()) {
    LocalRedirect('/');
}
\Bitrix\Main\Loader::includeModule('iblock');
$IBLOCK_ID = 42;

$el = new CIBlockElement;
$arProps = [];

$rsElement = CIBlockElement::getList([], ['IBLOCK_ID' => 4],
    false, false, ['ID', 'NAME']);
while ($ob = $rsElement->GetNextElement()) {
    $arFields = $ob->GetFields();
    $key = str_replace(['»', '«', '(', ')'], '', $arFields['NAME']);
    $key = strtolower($key);
    $arKey = explode(' ', $key);
    $key = '';
    foreach ($arKey as $part) {
        if (strlen($part) > 2) {
            $key .= trim($part) . ' ';
        }
    }
    $key = trim($key);
    $arProps['OFFICE'][$key] = $arFields['ID'];
}

$rsProp = CIBlockPropertyEnum::GetList(
    ["SORT" => "ASC", "VALUE" => "ASC"],
    ['IBLOCK_ID' => $IBLOCK_ID]
);
while ($arProp = $rsProp->Fetch()) {
    $key = trim($arProp['VALUE']);
    $arProps[$arProp['PROPERTY_CODE']][$key] = $arProp['ID'];
}

$rsElements = CIBlockElement::GetList([], ['IBLOCK_ID' => $IBLOCK_ID], false, false, ['ID']);
while ($element = $rsElements->GetNext()) {
    CIBlockElement::Delete($element['ID']);
}

/************* ПАРСЕР НАЧИНАЕТСЯ ЗДЕСЬ *************/ 

function processFile($filePath, &$el, &$arProps, $USER, $IBLOCK_ID) {
    if (!file_exists($filePath) || !is_readable($filePath)) {
        echo "Невозможно открыть файл " . strval($filePath);
        return;
    }

    $handle = fopen($filePath, "r");
    if (!$handle) {
        echo "Произошла ошибка при открытии файла " . strval($filePath);
        return;
    }

    $row = 0;
    while (($data = fgetcsv($handle)) !== false) {
        if ($row++ == 0) continue; 

        $PROP = [
            'ACTIVITY' => sanitizeData($data[9]),
            'FIELD' => sanitizeData($data[11]),
            'OFFICE' => sanitizeData($data[1]),
            'EMAIL' => sanitizeData($data[12]),
            'LOCATION' => sanitizeData($data[2]),
            'TYPE' => sanitizeData($data[8]),
            'SALARY_TYPE' => '',
            'SALARY_VALUE' => sanitizeData($data[7]),
            'REQUIRE' => sanitizeData($data[4]),
            'DUTY' => sanitizeData($data[5]),
            'CONDITIONS' => sanitizeData($data[6]),
            'SCHEDULE' => sanitizeData($data[10]),
            'DATE' => date('d.m.Y'),
        ];

        processOffice($PROP, $arProps, $data);
        processSalary($PROP, $arProps);
        processValues($PROP);

        $arLoadProductArray = [
            "MODIFIED_BY" => $USER->GetID(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $IBLOCK_ID,
            "PROPERTY_VALUES" => $PROP,
            "NAME" => sanitizeData($data[3]),
            "ACTIVE" => !empty(end($data)) ? 'Y' : 'N',
        ];

        if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
            echo "Добавлен элемент с ID: " . $PRODUCT_ID . "<br>";
        } else {
            echo "Error: " . $el->LAST_ERROR . '<br>';
        }
    }
    fclose($handle);
}

function sanitizeData($value) {
    return trim(str_replace('\n', '', $value));
}

function processValues(&$PROP) {
    foreach ($PROP as &$value) {
        $value = is_array($value) ? array_map('trim', $value) : trim($value);
    }
}

function processBulletList($value) {
    if (stripos($value, '•') !== false) {
        $value = explode('•', $value);
        array_splice($value, 0, 1); 
        $value = array_map('trim', $value);
    }
    return $value;
}

function processOffice(&$PROP, $arProps, $data) {
    $officeValue = strtolower($PROP['OFFICE']);
    $arSimilar = [];

    foreach ($arProps['OFFICE'] ?? [] as $propKey => $propVal) {
        if ($officeValue == 'центральный офис') {
            $PROP['OFFICE'] = 'свеза ' . $data[2];
        } elseif ($officeValue == 'лесозаготовка') {
            $PROP['OFFICE'] = 'свеза ресурс ' . $officeValue;
        } elseif ($officeValue == 'свеза тюмень') {
            $PROP['OFFICE'] = 'свеза тюмени';
        } elseif (stripos($propKey, $PROP['OFFICE']) !== false || similar_text($propKey, $PROP['OFFICE']) > 50) {
            $PROP['OFFICE'] = $propVal;
            break;
        }
        $arSimilar[similar_text($PROP['OFFICE'], $propKey)] = $propVal;
    }

    if (!is_numeric($PROP['OFFICE']) && !empty($arSimilar)) {
        ksort($arSimilar);
        $PROP['OFFICE'] = array_pop($arSimilar);
    }
}

function processSalary(&$PROP, $arProps) {
    if ($PROP['SALARY_VALUE'] == '-' || $PROP['SALARY_VALUE'] == 'по договоренности') {
        $PROP['SALARY_VALUE'] = '';
        $PROP['SALARY_TYPE'] = $PROP['SALARY_VALUE'] == 'по договоренности' ? $arProps['SALARY_TYPE']['договорная'] : '';
        return;
    }

    $arSalary = explode(' ', $PROP['SALARY_VALUE']);
    if (in_array($arSalary[0], ['от', 'до'])) {
        $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE'][$arSalary[0]];
        array_shift($arSalary);
    } else {
        $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE']['='];
    }
    $PROP['SALARY_VALUE'] = implode(' ', $arSalary);
}


parseVacancyCSV("vacancy.csv", $el, $arProps, $USER, $IBLOCK_ID);

