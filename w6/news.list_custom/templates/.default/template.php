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

<div class="news-list">
    <?php if (!empty($arResult['ITEMS'])): ?>
        <?php foreach ($arResult['ITEMS'] as $iblockId => $items): ?>
            <h2><?= $iblockId ?></h2>
            <ul>
                <?php foreach ($items as $item): ?>
                    <li>
                        <a href="<?= $item['DETAIL_PAGE_URL'] ?>">
                            <?= $item['NAME'] ?>
                        </a>
                        <p><?= $item['PREVIEW_TEXT'] ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No items found</p>
    <?php endif; ?>
</div>
