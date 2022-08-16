<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

$request = HttpApplication::getInstance()->getContext()->getRequest();

$moduleId = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);

Loader::includeModule($moduleId);

$snakeCaseModuleId = str_replace(".", "_", $moduleId);


if (!empty($request["apply"])) {
    Option::set($moduleId, "reviews_iblock_code", $request["iblock"]);
    Option::set($moduleId, "cities_region_code", $request["section"]);
} elseif (!empty($request["default"])) {
    Option::delete($moduleId, array("name" => "reviews_iblock_code"));
    Option::delete($moduleId, array("name" => "cities_region_code"));
}

$reviewsIblockCode = Option::get($moduleId, "reviews_iblock_code");
$citiesRegionCode = Option::get($moduleId, "cities_region_code");

$arIblockReviews = \Bitrix\Iblock\IblockTable::getList(
    array(
        "select" => array("NAME", "CODE"),
        "filter" => array('=IBLOCK_TYPE_ID' => 'reviews')
    )
)->fetchAll();

$arReviewsIblockType = [];

foreach ($arIblockReviews as $item) {
    $arReviewsIblockType[$item["CODE"]] = $item["NAME"];
}

$citiesIBlockId = \Bitrix\Iblock\IblockTable::getList(
    array(
        "select" => array("ID"),
        "filter" => array('=IBLOCK_TYPE_ID' => 'cities')
    )
)->fetch()["ID"];

$arCitiesIblockSections = \Bitrix\Iblock\SectionTable::getList([
    "select" => ["NAME", "CODE"],
    "filter" => array("=IBLOCK_ID" => $citiesIBlockId)
])->fetchAll();

$arCitiesRegions = [];

foreach ($arCitiesIblockSections as $item) {
    $arCitiesRegions[$item["CODE"]] = $item["NAME"];
}

$aTabs = array(
    array(
        "DIV" => $snakeCaseModuleId . "_edit",
        "TAB" => Loc::GetMessage("TEST_MODULE_TAB_LABEL"),
        "TITLE" => Loc::GetMessage("TEST_MODULE_TAB_TITLE"),
        "OPTIONS" => array(
            Loc::GetMessage("TEST_MODULE_OPTION_IBLOCK"),
            array(
                "iblock",
                Loc::GetMessage("TEST_MODULE_OPTION_IBLOCK"),
                $reviewsIblockCode,
                array("selectbox", $arReviewsIblockType)
            ),
            Loc::GetMessage("TEST_MODULE_OPTION_SECTION"),
            array(
                "section",
                Loc::GetMessage("TEST_MODULE_OPTION_SECTION"),
                $citiesRegionCode,
                array("selectbox", $arCitiesRegions)
            )
        )
    )
);

$tabControl = new CAdminTabControl(
    "tabControl",
    $aTabs
);

$tabControl->Begin(); ?>

    <form action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= $moduleId ?>&lang=<?= LANG ?>" method="post">

        <?php
        foreach ($aTabs as $aTab) {

            if ($aTab["OPTIONS"]) {

                $tabControl->BeginNextTab();

                __AdmSettingsDrawList($moduleId, $aTab["OPTIONS"]);
            }
        }

        $tabControl->Buttons();
        ?>

        <input type="submit" name="apply" value="<? echo(Loc::GetMessage("TEST_MODULE_OPTION_FORM_APPLY")); ?>"
               class="adm-btn-save"/>
        <input type="submit" name="default" value="<? echo(Loc::GetMessage("TEST_MODULE_OPTION_FORM_DEFAULT")); ?>"/>

        <?php
        echo(bitrix_sessid_post());
        ?>

    </form>

<?php

$tabControl->End();