<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */


/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 180;

$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
$arParams['IBLOCK_PROP'] = intval($arParams['IBLOCK_PROP']);


if($arParams['IBLOCK_ID'] > 0 && $arParams['IBLOCK_PROP'] > 0 && $this->StartResultCache(false, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups())))
{
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}
	//SELECT
	$arSelect = array(
		"ID",
		"IBLOCK_ID",
		"CODE",
		"IBLOCK_SECTION_ID",
		"NAME",
		"PREVIEW_TEXT",
		"PREVIEW_PICTURE",
		"DETAIL_PICTURE",
		"DETAIL_PAGE_URL",
	);
	//WHERE
	$arFilter = array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"ACTIVE_DATE" => "Y",
		"ACTIVE"=>"Y",
		"CHECK_PERMISSIONS"=>"Y",
		"!PROPERTY_".$arParams['IBLOCK_PROP'] => false,
	);
	if($arParams["PARENT_SECTION"]>0)
	{
		$arFilter["SECTION_ID"] = $arParams["PARENT_SECTION"];
		$arFilter["INCLUDE_SUBSECTIONS"] = "Y";
	}
	//ORDER BY
	$arSort = array(
		"RAND"=>"ASC",
	);
	//EXECUTE
	$rsIBlockElement = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
	$rsIBlockElement->SetUrlTemplates($arParams["DETAIL_URL"]);
	
	if($arResult = $rsIBlockElement->GetNext())
	{	
		$arResult["PICTURE"] = CFile::GetFileArray($arResult["PREVIEW_PICTURE"]);
		$arResult["PICTURE"] = CFile::ResizeImageGet($arResult['DETAIL_PICTURE'], array('width'=>$arParams['IMG_WIDTH'], 'height'=>$arParams['IMG_HEIGHT']),
		BX_RESIZE_IMAGE_PROPORTIONAL, true); 
	
		if(!is_array($arResult["PICTURE"]))
			$arResult["PICTURE"] = CFile::GetFileArray($arResult["DETAIL_PICTURE"]);

		$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arResult["IBLOCK_ID"], $arResult["ID"]);
		$arResult["IPROPERTY_VALUES"] = $ipropValues->getValues();

		if ($arResult["PICTURE"])
		{	
			$arResult["PICTURE"]["ALT"] = $arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"];
			if ($arResult["PICTURE"]["ALT"] == "")
				$arResult["PICTURE"]["ALT"] = $arResult["NAME"];
			$arResult["PICTURE"]["TITLE"] = $arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"];
			if ($arResult["PICTURE"]["TITLE"] == "")
				$arResult["PICTURE"]["TITLE"] = $arResult["NAME"];
				
		}

		$this->SetResultCacheKeys(array(
		));
		$this->IncludeComponentTemplate();
	}
	else
	{
		$this->AbortResultCache();
	}/*
	if($arResult = $rsIBlockElement->GetNext() )
	{


		$arResult["PICTURE"] = CFile::ResizeImageGet($uInfo['DETAIL_PICTURE'], array('width'=>$arParams['IMG_WIDTH'], 'height'=>$arParams['IMG_HEIGHT']),

		BX_RESIZE_IMAGE_PROPORTIONAL, true); 
		$tmpImage = CFile::ResizeImageGet($image, array("width" => 150, "height" => 150), BX_RESIZE_IMAGE_PROPORTIONAL, false);
		$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arResult["IBLOCK_ID"], $arResult["ID"]);
		$arResult["IPROPERTY_VALUES"] = $ipropValues->getValues();

		$this->SetResultCacheKeys(array(
		));
		$this->IncludeComponentTemplate();
	}
	else
	{
		$this->AbortResultCache();
	}*/
}
?>
