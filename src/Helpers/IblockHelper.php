<?php

namespace Otdel\Helpers;

class IblockHelper extends  Helper
{

    /**
     * @param string $code
     * @param null|string $iBlockType
     *
     * @throws HelperException
     *
     * @return int
     */
    public function getIblockIdByCode($code, $iBlockType = null)
    {
        if (!$code) {
            throw new HelperException('Не задан код инфоблока');
        }

        $filter = [
            'CODE'              => $code,
            'CHECK_PERMISSIONS' => 'N',
        ];

        if ($iBlockType !== null) {
            $filter['TYPE'] = $iBlockType;
        }

        if(\CModule::IncludeModule('iblock')) {
            $iblock = (new \CIBlock())->GetList([], $filter)->fetch();

            if (!$iblock['ID']) {
                throw new HelperException("Не удалось найти инфоблок с кодом '{$code}'");
            }

            return $iblock['ID'];
        }
        else {
            throw new HelperException("Не установлен модуль iblock");
        }

    }

    /**
     * @param array $codes
     * @param null|string $iBlockType
     * @return array $iblocks
     *
     * @throws HelperException
    */
    public function getIblockIDs($codes,$iBlockType = null) {
        if (!$codes) {
            throw new HelperException('Не заданы коды инфоблоков');
        }

        $filter = [
            'CODE'              => $codes,
            'CHECK_PERMISSIONS' => 'N',
        ];
        if ($iBlockType !== null) {
            $filter['TYPE'] = $iBlockType;
        }

        if(\CModule::IncludeModule('iblock')) {
            $arResult = $this->dbResultFetch((new \CIBlock())->GetList([], $filter));
            $iblocks = [];
            foreach ($arResult as $iblock) {
                $iblocks[$iblock['CODE']] = $iblock['ID'];
            }

            return $iblocks;
        }
        else {
            throw new HelperException("Не установлен модуль iblock");
        }

    }

}