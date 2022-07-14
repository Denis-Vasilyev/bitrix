<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Форма обратной связи - тестовое");

$APPLICATION->IncludeComponent("dvasilyev:feedback-form", "", array(), false);

$APPLICATION->IncludeComponent(
    "bitrix:highloadblock.list",
    "",
    array(
        "COMPONENT_TEMPLATE" => ".default",
        "BLOCK_ID" => 1,
        "ROWS_PER_PAGE" => 10,
        "DETAIL_URL" => ""
    )
);


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");