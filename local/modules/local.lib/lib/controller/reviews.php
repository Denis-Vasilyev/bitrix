<?php

namespace Local\Lib\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Iblock\Elements\ElementReviewsTable;
use Bitrix\Main\Loader;

class Reviews extends Controller
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'getRatingList' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    )
                ]
            ]
        ];
    }

    /**
     * @param integer $limit
     * @param integer $offset
     * @return array
     */
    public static function getRatingListAction(int $limit = null, int $offset = null): array
    {
        Loader::includeModule("iblock");

        $reviews = ElementReviewsTable::getList([
            "select" => [
                "CITY.ELEMENT.IBLOCK_SECTION_ID",
                "CITY.ELEMENT.NAME",
                "RATING.VALUE",
                "INNER_SECTION.NAME"
            ],
            "filter" => [],
            "runtime" => [
                'INNER_SECTION' => [
                    'data_type' => \Bitrix\Iblock\SectionTable::class,
                    'reference' => [
                        '=this.CITY.ELEMENT.IBLOCK_SECTION_ID' => 'ref.ID'
                    ],
                    'join_type' => 'LEFT'
                ]
            ],
            'count_total' => true,
            'limit'   => $limit,
            'offset'  => $offset
        ]);

        $res = $reviews->fetchAll();
        $res["all_count"] = $reviews->getCount();

        return $res;
    }
}