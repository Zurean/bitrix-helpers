<?php

namespace OIP\Helpers;

class IblockElementHelper extends Helper
{

    /**
     * @param null|string $iblockCode
     * @param null|array $filterParams
     * @param null|array $selectParams
     * @param null|array $ufProps
     * @return array $iblockElements
     *
     * @throws HelperException
     * */
    public function getElemetns($iblockCode = null, $filterParams = [], $selectParams = [], $ufProps = []) {

        $filterDefault = [
            'CHECK_PERMISSION' => 'N'
        ];

        $selectDefault = [
            'ID','NAME','CODE','IBLOCK_ID','IBLOCK_SECTION_ID','SORT','LIST_PAGE_URL','SECTION_PAGE_URL',
            'DETAIL_PAGE_URL','ACTIVE','PREVIEW_TEXT','DETAIL_TEXT','PREVIEW_PICTURE','DETAIL_PICTURE'];

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
            $iblockElements = $this->addPictures($this->dbResultFetch((new \CIBlockElement())->GetList([],$filter,false,false,$select)));

        }
        else {
            throw new HelperException('Не установлен модуль "iblock"');
        }

        return $iblockElements;
    }

    /**
     * @param string $code
     * @param null|string $iblockCode
     * @return array
     *
     * @throws HelperException
     * */
    public function getElementByCode($code, $iblockCode = null) {
        if(!$code) {
            throw new HelperException('Не задан код элемента');
        }

        return $this->getElemetns($iblockCode, ['CODE'=>$code])[0];
    }

    /**
     * @param int $id
     * @param null|string $iblockCode
     * @return array
     *
     * @throws HelperException
     * */
    public function getElementByID($id, $iblockCode = null) {
        if(!$id) {
            throw new HelperException('Не задан ID элемента');
        }

        return $this->getElemetns($iblockCode, ['ID'=>$id])[0];
    }

    /**
     * @param string $code
     * @param null|string $iblockCode
     * @return int
     *
     * @throws HelperException
     * */
    public function getElementIDByCode($code,$iblockCode = null) {
        return $this->getElementByCode($code,$iblockCode)['ID'];
    }

    /**
     * @param array $elements
     * @return array $els
    */
    protected function addPictures($elements) {
        $els = $elements;

        if(!empty($els)) {
            $arPicIDs = [];
            foreach($els as $element) {
                if($element['PREVIEW_PICTURE']) {
                    $arPicIDs[$element['PREVIEW_PICTURE']] = $element['PREVIEW_PICTURE'];
                }
                if($element['DETAIL_PICTURE']) {
                    $arPicIDs[$element['DETAIL_PICTURE']] = $element['DETAIL_PICTURE'];
                }
            }

            $arFiles = $this->getFiles(implode(',',$arPicIDs));

            if(!empty($arFiles)) {
                foreach($els as $key => $element) {
                    if($element['PREVIEW_PICTURE']) {
                        $els[$key]['PREVIEW_PICTURE'] = $arFiles[$els[$key]['PREVIEW_PICTURE']];
                    }
                    if($element['DETAIL_PICTURE']) {
                        $els[$key]['DETAIL_PICTURE'] = $arFiles[$els[$key]['DETAIL_PICTURE']];
                    }
                }
            }
        }

        return $els;
    }

    /**
     * @param string $code
     * @param string $prop
     * @param int $id
     * @return array
     *
     * @throws HelperException
    */
    public function getLinkedIDs($code,$prop,$id) {


        $finalFilter = ['IBLOCK_CODE'=>$code,'PROPERTY_'.$prop => $id];

        if(\CModule::IncludeModule('iblock')) {
           $linked = $this->dbResultFetch((new \CIBlockElement())->GetList([],$finalFilter,false,false,['ID','IBLOCK_CODE','IBLOCK_ID']));

           $linkedFinal = [];
           foreach($linked as $el) {
               $linkedFinal[] =  $el['ID'];
           }

           return $linkedFinal;

        }
        else {
            throw new HelperException('Не установлен модуль "iblock"');
        }
    }
}