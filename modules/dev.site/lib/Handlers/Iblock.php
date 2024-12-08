<?php

namespace Only\Site\Handlers;

class Iblock
{
    public function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        $iQuality = 95;
        $iWidth = 1000;
        $iHeight = 1000;

        // Получаем свойства инфоблока
        $dbIblockProps = \Bitrix\Iblock\PropertyTable::getList([
            'select' => ['*'],
            'filter' => ['IBLOCK_ID' => $arFields['IBLOCK_ID']]
        ]);

        // Выбираем свойства типа файл (F)
        $arUserFields = [];
        while ($arIblockProps = $dbIblockProps->Fetch()) {
            if ($arIblockProps['PROPERTY_TYPE'] === 'F') {
                $arUserFields[] = $arIblockProps['ID'];
            }
        }

        // Масштабируем изображения
        foreach ($arUserFields as $iFieldId) {
            foreach ($arFields['PROPERTY_VALUES'][$iFieldId] as &$file) {
                if (!empty($file['VALUE']['tmp_name'])) {
                    $sTempName = $file['VALUE']['tmp_name'] . '_temp';
                    $res = \CFile::ResizeImageFile(
                        $file['VALUE']['tmp_name'],
                        $sTempName,
                        ["width" => $iWidth, "height" => $iHeight],
                        BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
                        false,
                        $iQuality
                    );

                    if ($res) {
                        rename($sTempName, $file['VALUE']['tmp_name']);
                    }
                }
            }
        }

        // Дополнительная обработка свойств инфоблока
        if ($arFields['CODE'] === 'brochures') {
            $RU_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_RU');
            $EN_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_EN');

            if ($arFields['IBLOCK_ID'] == $RU_IBLOCK_ID || $arFields['IBLOCK_ID'] == $EN_IBLOCK_ID) {
                \CModule::IncludeModule('iblock');
                $arFiles = [];

                foreach ($arFields['PROPERTY_VALUES'] as $id => &$arValues) {
                    $arProp = \CIBlockProperty::GetByID($id, $arFields['IBLOCK_ID'])->Fetch();

                    if ($arProp['PROPERTY_TYPE'] === 'F' && $arProp['CODE'] === 'FILE') {
                        foreach ($arValues as $key => $value) {
                            if (strpos($key, 'n') === 0 && !empty($value)) {
                                $arFiles[] = $value;
                            }
                        }
                    }
                }
            }
        }
    }
}
