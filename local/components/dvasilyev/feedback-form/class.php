<?php

use \Bitrix\Main\Loader;
use \Bitrix\Main\Engine\Contract\Controllerable;

class CFBForm extends CBitrixComponent implements Controllerable
{
    const TEST_FORM_HLBLOCK_ID = 1;

    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    // Обязательный метод
    public function configureActions()
    {
        // Сбрасываем фильтры по-умолчанию (ActionFilter\Authentication и ActionFilter\HttpMethod)
        // Предустановленные фильтры находятся в папке /bitrix/modules/main/lib/engine/actionfilter/
        return [
            'sendForm' => [ // Ajax-метод
                'prefilters' => [],
            ],
        ];
    }

    // Ajax-методы должны быть с постфиксом Action
    public function sendFormAction($formData)
    {
        //CSRF-защита
        if (bitrix_sessid() !== $formData['SESSID']) {
            return "Пожалуста, обновите страницу.";
        }

        try {
            Loader::includeModule("highloadblock");

            $hlBlock = \Bitrix\Highloadblock\HighloadBlockTable::getById(self::TEST_FORM_HLBLOCK_ID)->fetch();

            $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlBlock);

            $entityDataClass = $entity->getDataClass();

            $data = array(
                "UF_FIO" => $formData['FIO'],
                "UF_EMAIL" => $formData['EMAIL'],
                "UF_PHONE" => $formData['PHONE'],
                "UF_QUESTION" => $formData['QUESTION'],
                "UF_DATE" => date("d.m.Y H:i:s")
            );

            $result = $entityDataClass::add($data);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return true;
    }
}