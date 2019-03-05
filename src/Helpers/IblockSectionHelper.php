<?php
namespace OIP\Helpers;

class IblockSectionHelper extends  Helper
{

    /**
     * @param null|string $iblockCode
     * @param null|array $filterParams
     * @param null|array $selectParams
     * @param null|array $ufProps
     * @param null|array $sortParams
     * @return array $iblockSections
     *
     * @throws HelperException
     * */
    public function getSections($iblockCode = null, $filterParams = [], $selectParams = [], $ufProps = [],
                                $sortParams = [])
    {

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
            $iblockSections = $this->dbResultFetch((new \CIBlockSection())
                ->GetList($sortParams,$filter,false,$select));

            $arPicIDs = [];
            foreach($iblockSections as $section) {
                if($section['PICTURE']) {
                    $arPicIDs[$section['PICTURE']] = $section['PICTURE'];
                }
                if($section['DETAIL_PICTURE']) {
                    $arPicIDs[$section['DETAIL_PICTURE']] = $section['DETAIL_PICTURE'];
                }
            }

            $arFiles = [];
            if(!empty($arPicIDs)) {
                $arFiles = $this->getPictures($arPicIDs);
            }

            if(!empty($arFiles)) {
                foreach($iblockSections as $key => $section) {
                    if($section['PICTURE']) {
                        $iblockSections[$key]['PICTURE'] = $arFiles[$iblockSections[$key]['PICTURE']];
                    }
                    if($section['DETAIL_PICTURE']) {
                        $iblockSections[$key]['DETAIL_PICTURE'] = $arFiles[$iblockSections[$key]['DETAIL_PICTURE']];
                    }
                }
            }
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
     * @param int $id
     * @param null|string $iblockCode
     * @return array
     *
     * @throws HelperException
     * */
    public function getSectionByID($id, $iblockCode = null) {
        if(!$id) {
            throw new HelperException('Не задан ID раздела');
        }

        return $this->getSections($iblockCode, ['ID'=>$id])[0];
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

    /**
     * @param null|string $iblockCode
     * @param null|array $filterParams
     * @param null|array $selectParams
     * @param null|array $ufProps
     * @return array
     * @throws HelperException
     * */
    public function getSectionTree($iblockCode = null, $filterParams = [], $selectParams = [], $ufProps = []) {
        $sortParams = ["left_margin"=>"asc"];
        return $this->getSections($iblockCode,$filterParams,$selectParams,$ufProps,$sortParams);
    }

    /**
     * @param int $iblockID
     * @param null|int $sectionID
     * @param array $fields
     * @return array $arUfValues
     *
     * @throws HelperException
     * */
    public function getUF($iblockID, $sectionID = null, $fields = []) {
        if(!$iblockID) {
            throw new HelperException('Не задан ID инфоблока');
        }

        if(empty($fields)) {
            $ufFields = $this->getUFFields($iblockID);
        }
        else {
            $ufFields = $fields;
        }

        $arFilter = ['IBLOCK_ID'=>$iblockID];
        if($sectionID) {
            $arFilter['ID'] = $sectionID;
        }
        $arSelect = array_merge(['ID','NAME'],$ufFields);

        $arUfValues = [];
        $dbUF = \CIBlockSection::GetList([],$arFilter,false,$arSelect);
        while($ufValue = $dbUF->Fetch()) {
            $arUfValues[$ufValue['ID']] = $ufValue;
        }

        return $arUfValues;
    }

    /**
     * @param int $iblockID
     * @param int $sectionID
     * @param array $fields
     * @return array
     *
     * @throws HelperException
    */
    public function getUFOne($iblockID, $sectionID, $fields = []) {
        if(!$sectionID) {
            throw new HelperException('Не задан ID раздела');
        }

        $res = $this->getUF($iblockID, $sectionID, $fields);

        return $this->getUF($iblockID, $sectionID, $fields)[$sectionID];
    }

    /**
     * @param array $IDs
     * @return array $arFiles
     *
     * @throws HelperException
    */
    public function getPictures($IDs) {
        if(!is_array($IDs)) {
            throw new HelperException('Неверный тип данных');
        }
        $arFiles = $this->getFiles(implode(',',$IDs));

        return $arFiles;
    }

    public function getPicture($id) {
        $IDs = [$id];
        return $this->getPictures($IDs)[$id];
    }

    /**
     * @param int $iblockID
     * @return array $arUFFields
     *
     * @throws HelperException
     */
    protected function getUFFields($iblockID) {

        if(!$iblockID) {
            throw new HelperException('Не задан ID инфоблока');
        }

        $dbUf = \CUserTypeEntity::GetList([],[
            "ENTITY_ID" => "IBLOCK_".$iblockID."_SECTION"
        ]);

        $arUFFields = [];
        while($uf = $dbUf->Fetch()) {
            $arUFFields[] = $uf['FIELD_NAME'];
        }

        return $arUFFields;
    }
}