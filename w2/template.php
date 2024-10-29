<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); ?>

<link href="<?=$templateFolder?>/style.css" rel="stylesheet">

<html>
<body>
<div class="contact-form">
    <div class="contact-form__head">
        <div class="contact-form__head-title"><?=$arResult["FORM_TITLE"]?></div>
        <div class="contact-form__head-text"><?=$arResult["FORM_DESCRIPTION"]?></div>
    </div>

    <?php if ($arResult["isFormNote"] == "Y"): ?>
        <p class="contact-form__success"><?=$arResult["FORM_NOTE"]?></p>
    <?php else: ?>
        <?= $arResult["FORM_HEADER"] ?>
        <form class="contact-form__form" action="<?= POST_FORM_ACTION_URI ?>" method="POST">
            <div class="contact-form__form-inputs">
                <?php foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion): ?>
                    <div class="input contact-form__input">
                        <label class="input__label" for="<?= $FIELD_SID ?>">
                            <div class="input__label-text"><?= $arQuestion["CAPTION"] ?><?= $arQuestion["REQUIRED"] ? "*" : "" ?></div>
                            <?= $arQuestion["HTML_CODE"] ?>
                            <?php if ($arQuestion["REQUIRED"]): ?>
                                <div class="input__notification">Поле обязательно для заполнения</div>
                            <?php endif; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="contact-form__bottom">
                <div class="contact-form__bottom-policy">Нажимая &laquo;Отправить&raquo;, Вы подтверждаете, что ознакомлены, полностью согласны и принимаете условия &laquo;Согласия на обработку персональных данных&raquo;.</div>
                <button class="form-button contact-form__bottom-button" type="submit" data-success="Отправлено" data-error="Ошибка отправки">
                    <div class="form-button__title"><?=htmlspecialcharsbx(trim($arResult["arForm"]["BUTTON"]) == '' ? GetMessage("FORM_ADD") : $arResult["arForm"]["BUTTON"]);?></div>
                </button>
            </div>
        </form>
        <?= $arResult["FORM_FOOTER"] ?>
    <?php endif; ?>
</div>
</body>
</html>
