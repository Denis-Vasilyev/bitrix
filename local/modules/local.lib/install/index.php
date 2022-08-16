<?php

IncludeModuleLangFile(__FILE__);

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Engine\CurrentUser;

class Local_Lib extends CModule
{

    public $MODULE_VERSION = "1.0.0";
    public $MODULE_VERSION_DATE = "2022-08-14 00:00:00";
    public $MODULE_NAME = "Тестовый модуль";
    public $MODULE_DESCRIPTION = "Тестовый модуль для разработчиков, можно использовать как основу для разработки новых модулей для 1С:Битрикс";
    public $PARTNER_NAME = "Денис Васильев";
    public $PARTNER_URI = "http://localhost";
    public $errors;

    function __construct()
    {
        global $APPLICATION;

        $this->MODULE_ID = strtolower(str_replace("_", ".", get_class($this)));

        $this->MODULE_NAME = Loc::getMessage("TEST_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("TEST_MODULE_DESCRIPTION");

        if (file_exists(__DIR__ . "/version.php")) {

            $arModuleVersion = array();

            include_once(__DIR__ . "/version.php");

            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
            $this->PARTNER_NAME = $arModuleVersion["TEST_MODULE_PARTNER_NAME"];
            $this->PARTNER_URI = $arModuleVersion["TEST_MODULE_PARTNER_URI"];
        }

        Loader::includeModule("iblock");
    }

    function DoInstall()
    {
        try{
            $this->InstallIblocks();
        } catch (Exception $e) {
            return false;
        }

        ModuleManager::RegisterModule($this->MODULE_ID);

        return true;
    }

    function DoUninstall()
    {
        try {
            $this->UnInstallIblocks();
        } catch (Exception $e) {
            return false;
        }

        ModuleManager::UnRegisterModule($this->MODULE_ID);

        return true;
    }

    /**
     * @throws Exception
     */
    function InstallIblocks(): bool
    {
        global $DB;

        //тип инфоблока "Отзывы"
        $arFieldsReviews = Array(
            "ID"=>"reviews",
            "SECTIONS"=>"N",
            "IN_RSS"=>"N",
            "SORT"=>1,
            "LANG" => array(
                "ru" => array(
                    "NAME" => "Отзывы",
                    "SECTION_NAME" => "Отзывы",
                    "ELEMENT_NAME" => "Отзыв"
                ),
                "en" => array(
                    "NAME" => "Reviews",
                    "SECTION_NAME" => "Reviews",
                    "ELEMENT_NAME" => "Review"
                )
            )
        );

        $obReviewsIBlockType = new CIBlockType;

        $DB->StartTransaction();

        $res = $obReviewsIBlockType->Add($arFieldsReviews);

        if(!$res)
        {
            $DB->Rollback();
            throw new Exception($obReviewsIBlockType->LAST_ERROR);
        }

        //тип инфоблока "Города"
        $arFieldsCities = Array(
            "ID"=>"cities",
            "SECTIONS"=>"Y",
            "IN_RSS"=>"N",
            "SORT"=>2,
            "LANG" => array(
                "ru" => array(
                    "NAME" => "Города",
                    "SECTION_NAME" => "Область",
                    "ELEMENT_NAME" => "Город"
                ),
                "en" => array(
                    "NAME" => "Cities",
                    "SECTION_NAME" => "Region",
                    "ELEMENT_NAME" => "City"
                )
            )
        );

        $obCitiesIBlockType = new CIBlockType;

        $res = $obCitiesIBlockType->Add($arFieldsCities);

        if(!$res)
        {
            $DB->Rollback();
            throw new Exception($obCitiesIBlockType->LAST_ERROR);
        }

        $citiesIBlockId = $this->CreateCitiesIBlock();
        $this->FillCitiesIBlock($citiesIBlockId);

        $reviewsIBlockId = $this->CreateReviewsIBlock($citiesIBlockId);
        $this->FillReviewsIBlock($reviewsIBlockId, $citiesIBlockId);

        $DB->Commit();

        return true;
    }

    /**
     * @throws Exception
     */
    function UnInstallIblocks()
    {
        global $DB;

        $DB->StartTransaction();

        $reviewsIblockType = "reviews";

        if(!CIBlock::Delete($this->getIBlockIdByCode($reviewsIblockType)))
        {
            $DB->Rollback();
            $mess = Loc::getMessage("IBLOCK_DELETE_ERROR") . " - $reviewsIblockType.";
            throw new Exception($mess);
        }

        if(!CIBlockType::Delete($reviewsIblockType))
        {
            $DB->Rollback();
            $mess = Loc::getMessage("IBLOCK_TYPE_DELETE_ERROR") . " - $reviewsIblockType.";
            throw new Exception($mess);
        }

        $citiesIblockType = "cities";

        if(!CIBlock::Delete($this->getIBlockIdByCode($citiesIblockType)))
        {
            $DB->Rollback();
            $mess = Loc::getMessage("IBLOCK_DELETE_ERROR") . " - $citiesIblockType.";
            throw new Exception($mess);
        }

        if(!CIBlockType::Delete($citiesIblockType))
        {
            $DB->Rollback();
            $mess = Loc::getMessage("IBLOCK_TYPE_DELETE_ERROR") . " - $citiesIblockType.";
            throw new Exception($mess);
        }

        $DB->Commit();

        return true;
    }

    /**
     * @throws Exception
     */
    private function CreateCitiesIBlock()
    {
        $ib = new CIBlock;

        $iblockType = "cities"; // Тип инфоблока
        $siteId = "s1"; // ID сайта

        // Настройка доступа
        $arAccess = array(
            "2" => "R", // Все пользователи
        );

        $arFields = array(
            "ACTIVE" => "Y",
            "NAME" => "Города",
            "CODE" => $iblockType,
            "API_CODE" => $iblockType,
            "REST_ON" => "Y",
            "IBLOCK_TYPE_ID" => $iblockType,
            "SITE_ID" => $siteId,
            "SORT" => "5",
            "GROUP_ID" => $arAccess, // Права доступа
            "FIELDS" => array(
                // Символьный код элементов
                "CODE" => array(
                    "IS_REQUIRED" => "Y", // Обязательное
                    "DEFAULT_VALUE" => array(
                        "UNIQUE" => "Y", // Проверять на уникальность
                        "TRANSLITERATION" => "Y", // Транслитерировать
                        "TRANS_LEN" => "30", // Максмальная длина транслитерации
                        "TRANS_CASE" => "L", // Приводить к нижнему регистру
                        "TRANS_SPACE" => "-", // Символы для замены
                        "TRANS_OTHER" => "-",
                        "TRANS_EAT" => "Y",
                        "USE_GOOGLE" => "N",
                    ),
                ),
                // Символьный код разделов
                "SECTION_CODE" => array(
                    "IS_REQUIRED" => "Y",
                    "DEFAULT_VALUE" => array(
                        "UNIQUE" => "Y",
                        "TRANSLITERATION" => "Y",
                        "TRANS_LEN" => "30",
                        "TRANS_CASE" => "L",
                        "TRANS_SPACE" => "-",
                        "TRANS_OTHER" => "-",
                        "TRANS_EAT" => "Y",
                        "USE_GOOGLE" => "N",
                    ),
                ),
                "DETAIL_TEXT_TYPE" => array(      // Тип детального описания
                    "DEFAULT_VALUE" => "html",
                ),
                "SECTION_DESCRIPTION_TYPE" => array(
                    "DEFAULT_VALUE" => "html",
                ),
                "IBLOCK_SECTION" => array(         // Привязка к разделам обязательноа
                    "IS_REQUIRED" => "Y",
                ),
                "LOG_SECTION_ADD" => array("IS_REQUIRED" => "Y"), // Журналирование
                "LOG_SECTION_EDIT" => array("IS_REQUIRED" => "Y"),
                "LOG_SECTION_DELETE" => array("IS_REQUIRED" => "Y"),
                "LOG_ELEMENT_ADD" => array("IS_REQUIRED" => "Y"),
                "LOG_ELEMENT_EDIT" => array("IS_REQUIRED" => "Y"),
                "LOG_ELEMENT_DELETE" => array("IS_REQUIRED" => "Y"),

            ),

            // Шаблоны страниц
            "LIST_PAGE_URL" => "#SITE_DIR#/$iblockType/",
            "SECTION_PAGE_URL" => "#SITE_DIR#/$iblockType/#SECTION_CODE#/",
            "DETAIL_PAGE_URL" => "#SITE_DIR#/$iblockType/#SECTION_CODE#/#ELEMENT_CODE#/",

            "INDEX_SECTION" => "Y", // Индексировать разделы для модуля поиска
            "INDEX_ELEMENT" => "Y", // Индексировать элементы для модуля поиска

            "VERSION" => 1, // Хранение элементов в общей таблице

            "ELEMENT_NAME" => "Город",
            "ELEMENTS_NAME" => "Города",
            "ELEMENT_ADD" => "Добавить город",
            "ELEMENT_EDIT" => "Изменить город",
            "ELEMENT_DELETE" => "Удалить город",
            "SECTION_NAME" => "Область",
            "SECTIONS_NAME" => "Области",
            "SECTION_ADD" => "Добавить область",
            "SECTION_EDIT" => "Изменить область",
            "SECTION_DELETE" => "Удалить область",

            "SECTION_PROPERTY" => "N", // Разделы каталога имеют свои свойства (нужно для модуля интернет-магазина)
        );

        $id = $ib->Add($arFields);

        if (!$id) {
            $mess = "&mdash; ошибка создания инфоблока \"Города\"";
            throw new Exception($mess);
        }

        return $id;
    }

    /**
     * @throws Exception
     */
    private function FillCitiesIBlock($iBlockId)
    {
        $citiesIblockData = array(
            "Липецкая область" => array("Липецк", "Данков", "Лебедянь"),
            "Московская область" => array("Москва", "Подольск", "Зеленоград")
        );

        $bs = new CIBlockSection;

        $el = new CIBlockElement;

        foreach ($citiesIblockData as $key => $val) {
            $arFields = Array(
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $iBlockId,
                "NAME" => $key,
                "CODE" => CUtil::translit($key,"ru")
            );

            $newSecId = $bs->Add($arFields);

            if(!$newSecId) {
                throw new Exception($bs->LAST_ERROR);
            }

            foreach ($val as $city) {
                $arLoadProductArray = Array(
                    "MODIFIED_BY" => CurrentUser::get()->getId(),
                    "IBLOCK_SECTION_ID" => $newSecId,
                    "IBLOCK_ID" => $iBlockId,
                    "NAME" => $city,
                    "CODE" => CUtil::translit($city,"ru"),
                    "ACTIVE" => "Y"
                );

                if(!$el->Add($arLoadProductArray)) {
                    throw new Exception($el->LAST_ERROR);
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    private function CreateReviewsIBlock($citiesIBlockId)
    {
        $ib = new CIBlock;

        $iblockType = "reviews"; // Тип инфоблока
        $siteId = "s1"; // ID сайта

        // Настройка доступа
        $arAccess = array(
            "2" => "R", // Все пользователи
        );

        $arFields = array(
            "ACTIVE" => "Y",
            "NAME" => "Отзывы",
            "CODE" => $iblockType,
            "API_CODE" => $iblockType,
            "REST_ON" => "Y",
            "IBLOCK_TYPE_ID" => $iblockType,
            "SITE_ID" => $siteId,
            "SORT" => "5",
            "GROUP_ID" => $arAccess, // Права доступа
            "FIELDS" => array(
                "DETAIL_TEXT_TYPE" => array(      // Тип детального описания
                    "DEFAULT_VALUE" => "html",
                ),
                "SECTION_DESCRIPTION_TYPE" => array(
                    "DEFAULT_VALUE" => "html",
                ),
                "IBLOCK_SECTION" => array(         // Привязка к разделам обязательноа
                    "IS_REQUIRED" => "N",
                ),
                "LOG_ELEMENT_ADD" => array("IS_REQUIRED" => "Y"),
                "LOG_ELEMENT_EDIT" => array("IS_REQUIRED" => "Y"),
                "LOG_ELEMENT_DELETE" => array("IS_REQUIRED" => "Y"),
            ),

            // Шаблоны страниц
            "LIST_PAGE_URL" => "#SITE_DIR#/$iblockType/",
            "SECTION_PAGE_URL" => "#SITE_DIR#/$iblockType/#SECTION_CODE#/",
            "DETAIL_PAGE_URL" => "#SITE_DIR#/$iblockType/#SECTION_CODE#/#ELEMENT_CODE#/",

            "INDEX_SECTION" => "Y", // Индексировать разделы для модуля поиска
            "INDEX_ELEMENT" => "Y", // Индексировать элементы для модуля поиска

            "VERSION" => 1, // Хранение элементов в общей таблице

            "ELEMENT_NAME" => "Рейтинг",
            "ELEMENTS_NAME" => "Рейтинги",
            "ELEMENT_ADD" => "Добавить рейтинг",
            "ELEMENT_EDIT" => "Изменить рейтинг",
            "ELEMENT_DELETE" => "Удалить рейтинг",

            "SECTION_PROPERTY" => "N", // Разделы каталога имеют свои свойства (нужно для модуля интернет-магазина)
        );

        $id = $ib->Add($arFields);

        if (!$id) {
            $mess = "&mdash; ошибка создания инфоблока \"Города\"";
            throw new Exception($mess);
        }

        $ibp = new CIBlockProperty;

        $arFields = array(
            "NAME" => "Рейтинг",
            "ACTIVE" => "Y",
            "SORT" => -777, // Сортировка
            "CODE" => "RATING",
            "PROPERTY_TYPE" => "N", // Число
            "IS_REQUIRED" => "Y",
            "FILTRABLE" => "Y", // Выводить на странице списка элементов поле для фильтрации по этому свойству
            "DEFAULT_VALUE" => 0,
            "IBLOCK_ID" => $id
        );
        $propId = $ibp->Add($arFields);
        if (!$propId) {
            $mess = "&mdash; Ошибка добавления свойства $arFields[NAME]";
            throw new Exception($mess);
        }

        $arFields = array(
            "NAME" => "Город",
            "ACTIVE" => "Y",
            "SORT" => -777, // Сортировка
            "CODE" => "CITY",
            "PROPERTY_TYPE" => "E", // Строка
            "IS_REQUIRED" => "Y",
            "FILTRABLE" => "Y", // Выводить на странице списка элементов поле для фильтрации по этому свойству
            "IBLOCK_ID" => $id,
            "LINK_IBLOCK_ID" => $citiesIBlockId,
        );
        $propId = $ibp->Add($arFields);
        if (!$propId) {
            $mess = "&mdash; Ошибка добавления свойства $arFields[NAME]";
            throw new Exception($mess);
        }

        return $id;

    }

    /**
     * @throws Exception
     */
    private function FillReviewsIBlock($reviewsIBlockId,$citiesIBlockId)
    {
        $arSelect = array("ID", "NAME", "CODE");
        $arFilter = array("IBLOCK_ID" => $citiesIBlockId, "ACTIVE" => "Y");
        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);

        $el = new CIBlockElement;

        while($ob = $res->GetNextElement())
        {
            $arFields = $ob->GetFields();

            $prop = ["RATING" => rand(1, 100), "CITY" => array("n0" => $arFields["ID"])];

            $arLoadProductArray = Array(
                "MODIFIED_BY" => CurrentUser::get()->getId(),
                "IBLOCK_ID" => $reviewsIBlockId,
                "NAME" => $arFields["NAME"],
                "CODE" => $arFields["CODE"],
                "PROPERTY_VALUES"=> $prop,
                "ACTIVE" => "Y"
            );

            if(!$el->Add($arLoadProductArray)) {
                throw new Exception($el->LAST_ERROR);
            }
        }
    }

    private function getIBlockIdByCode($code)
    {
        $res = CIBlock::GetList(
            array(),
            array(
                "CODE" => $code,
                "SITE_ID" => "s1"
            ), true
        );

        return $res->Fetch("ID");
    }
}