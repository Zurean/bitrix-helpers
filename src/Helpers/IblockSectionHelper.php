<?php
namespace Otdel\Helpers;

class IblockSectionHelper extends  Helper
{

    /**
     * @param null|string $iblockCode
     * @param null|array $filterParams
     * @param null|array $selectParams
     * @param null|array $ufProps
     * @return array $iblockSections
     *
     * @throws HelperException
     * */
    public function getSections($iblockCode = null, $filterParams = [], $selectParams = [], $ufProps = []) {

        $filterDefault = [
            'CHECK_PERMISSION' => 'N'
        ];

        $selectDefault = [
            'ID','NAME','CODE','IBLOCK_ID','IBLOCK_SECTION_ID','SORT','LIST_PAGE_URL','SECTION_PAGE_URL'
        ];

        if($iblockCode) {
            $filterDefault['IBLOCK_CODE'] = $iblockCode;
        }

        if(!empty($ufProps)) {
            if(!$iblockCode) {
                throw new HelperException('Не задан код инфоблока');
            }

            $iblockID = (new IblockHelper)->getIblockIdByCode($iblockCode);
            $filterDefault['IBLOCK_ID'] = $iblockID;
            $selectDefault = array_merge($ufProps,$selectDefault);
        }


        $filter = array_merge_recursive($filterParams,$filterDefault);
        $select = array_merge_recursive($selectParams,$selectDefault);

        if(\CModule::IncludeModule('iblock')) {
            $iblockSections = $this->dbResultFetch((new \CIBlockSection())->GetList([],$filter,false,$select));

        }
        else {
            throw new HelperException('Не установлен модуль "iblock"');
        }

        return $iblockSections;
    }

    /**
     * @param string $code
     * @param null|string $iblockCode
     * @return array
     *
     * @throws HelperException
     * */
    public function getSectionByCode($code, $iblockCode = null) {
        if(!$code) {
            throw new HelperException('Не задан код раздела');
        }

        return $this->getSections($iblockCode, ['CODE'=>$code])[0];
    }

    /**
     * @param string $code
     * @param null|string $iblockCode
     * @return int
     *
     * @throws HelperException
     * */
    public function getSectionIDByCode($code,$iblockCode = null) {
        return $this->getSectionByCode($code,$iblockCode)['ID'];
    }

    /**
     * @param int $iblockID
     * @return array $arTree
     *
     * @throws HelperException
     */
    public function getTree($iblockID) {
        if(!$iblockID) {
            throw new HelperException('Не задан ID инфоблока');
        }
        $filter = [
            'CHECK_PERMISSION' => 'N',
            'IBLOCK_ID' => $iblockID
        ];

        $select = [
            'ID','NAME','CODE','IBLOCK_ID','IBLOCK_SECTION_ID','SORT','LIST_PAGE_URL','DETAIL_PAGE_URL', 'TYPE'
        ];

        $arTree = [];
        if(\CModule::IncludeModule('iblock')) {
            $arMixed = $this->dbResultFetch((new \CIBlockSection)->GetMixedList([],$filter,false,$select));

            foreach($arMixed as $item) {
                if($item['TYPE'] == 'S') {
                    $item['SECTION_PAGE_URL'] = str_replace('#SECTION_CODE#',$item['CODE'],$item['SECTION_PAGE_URL']);
                    $arTree[] = $item;
                }
            }
            foreach($arTree as &$section) {
                foreach($arMixed as $item) {
                    if($item['TYPE'] == 'E' && $item['IBLOCK_SECTION_ID'] == $section['ID']) {
                        $item['DETAIL_PAGE_URL'] = str_replace(['#SECTION_CODE#','#ELEMENT_CODE#'],[$section['CODE'],$item['CODE']],$item['DETAIL_PAGE_URL']);
                        $section['ELEMENTS'][] = $item;
                    }
                }
            }
        }
        else {
            throw new HelperException('Не установлен модуль "iblock"');
        }

        return $arTree;
    }

    public function getUFByCode($ufCode) {
        
    }
}