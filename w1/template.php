<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>

<link rel="stylesheet" href="<?= $templateFolder ?>/common.css">

<div class="news-list">
<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?><br />
<?endif;?>

<div class="article-list article-list">
	<?php foreach ($arResult["ITEMS"] as $key=>$arItem): ?>
		<?
		$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
		$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
		?>

		<a class="article-item article-list__item" 
			href="<?= $arItem["DETAIL_PAGE_URL"] ?>" 
			data-anim="anim-3" 
			id="<?=$this->GetEditAreaId($arItem['ID']);?>">

			<div class="article-item__background">
				<?php if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arItem["PREVIEW_PICTURE"])):?>
					<img class="article-item__background"
						src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>" 
						border="0"
						width="<?=$arItem["PREVIEW_PICTURE"]["WIDTH"]?>"
						height="<?=$arItem["PREVIEW_PICTURE"]["HEIGHT"]?>"
						alt="<?=$arItem["PREVIEW_PICTURE"]["ALT"]?>"
						title="<?=$arItem["PREVIEW_PICTURE"]["TITLE"]?>"
					/>
				<?php endif; ?>
			</div>
			<div class="article-item__wrapper">
				<?if($arParams["DISPLAY_NAME"]!="N" && $arItem["NAME"]):?>
					<div class="article-item__title"><?= $arItem["NAME"] ?></div>
				<?php endif; ?>
				<?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]):?>
					<div class="article-item__content"><?= $arItem["PREVIEW_TEXT"] ?></div>
				<?endif;?>
			</div>
		</a>
	<?php endforeach; ?>
</div>

<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<br /><?=$arResult["NAV_STRING"]?>
<?endif;?>
</div>
