<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity;

class AvailableCarsComponent extends \CBitrixComponent
{
    public function __construct($component = null)
	{
		parent::__construct($component);
	}

    public function executeComponent()
    {
        if (!Loader::includeModule("highloadblock")) {
            ShowError("Модуль Highloadblock не подключен.");
            return;
        }

        $timeStart = $this->request->getQuery("time_start");
        $timeEnd = $this->request->getQuery("time_end");

        if (!$timeStart || !$timeEnd) {
            ShowError("Не указано время поездки.");
            return;
        }

        $timeStart = new \Bitrix\Main\Type\DateTime($timeStart);
        $timeEnd = new \Bitrix\Main\Type\DateTime($timeEnd);

        global $USER;
        if (!$USER->IsAuthorized()) {
            ShowError("Пользователь не авторизован.");
            return;
        }

        $userId = $USER->GetID();
        $userGroupIds = CUser::GetUserGroup($userId);

        $userPosition = $this->getUserPosition($userGroupIds);

        if (!$userPosition) {
            ShowError("Должность пользователя не определена.");
            return;
        }

        $allowedCategories = $this->getAllowedCategories($userPosition['UF_ALLOWED_CATEGORIES']);

        if (empty($allowedCategories)) {
            ShowError("Для вашей должности нет доступных категорий.");
            return;
        }

        $availableCars = $this->getAvailableCars($allowedCategories, $timeStart, $timeEnd);

        $this->arResult = [
            "CARS" => $availableCars,
        ];

        $this->includeComponentTemplate();
    }

    private function getUserPosition($userGroupIds)
    {
        $hlblock = $this->getHlblockByName("Positions");
        $entity = HighloadBlockTable::compileEntity($hlblock);
        $entityClass = $entity->getDataClass();

        $result = $entityClass::getList([
            "filter" => ["UF_GROUP_ID" => $userGroupIds],
            "limit" => 1,
        ])->fetch();

        return $result;
    }

    private function getAllowedCategories($allowedCategoryIds)
    {
        $hlblock = $this->getHlblockByName("Categories");
        $entity = HighloadBlockTable::compileEntity($hlblock);
        $entityClass = $entity->getDataClass();

        $result = $entityClass::getList([
            "filter" => ["ID" => $allowedCategoryIds],
            "select" => ["ID", "UF_NAME"],
        ])->fetchAll();

        return $result;
    }

    private function getAvailableCars($categories, $timeStart, $timeEnd)
    {
        $hlblock = $this->getHlblockByName("Cars");
        $entity = HighloadBlockTable::compileEntity($hlblock);
        $entityClass = $entity->getDataClass();

        $result = $entityClass::getList([
            "filter" => [
                "UF_CATEGORY_ID" => array_column($categories, "ID"),
                [
                    "LOGIC" => "OR",
                    ["UF_BUSY_START" => false],
                    [
                        "LOGIC" => "AND",
                        [
                            "<=UF_BUSY_END" => $timeStart,
                            ">=UF_BUSY_START" => $timeEnd,
                        ]
                    ]
                ],
            ],
            "select" => ["UF_MODEL", "UF_DRIVER", "UF_CATEGORY_ID"],
        ])->fetchAll();

        return $result;
    }

    private function getHlblockByName(string $name)
    {
        $hlblock = HighloadBlockTable::getList([
            "filter" => ["=NAME" => $name],
            "limit" => 1,
        ])->fetch();

        if (!$hlblock) {
            throw new \Exception("Highload-блок с именем '{$name}' не найден.");
        }

        return $hlblock;
    }
}