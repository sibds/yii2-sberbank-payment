<?php
use yii\helpers\Url;
?>
    <form action="https://alfabank.ru/Payment/Init" method="post" id="payment_alfabank_form">
        <input type="hidden" name="LMI_MERCHANT_ID" value="<?=$module->merchantId;?>" />
        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
        <input type="hidden" name="LMI_PAYMENT_AMOUNT" value="<?=$orderModel->getCost();?>" />
        <input type="hidden" name="LMI_CURRENCY" value="<?=$module->currency;?>" />
        <input type="hidden" name="LMI_PAYMENT_NO" value="<?=$orderModel->getId();?>" />
        <input type="hidden" name="LMI_PAYMENT_DESC" value="<?=$description;?>" />
        <input type="hidden" name="LMI_PAYMENT_NOTIFICATION_URL" value="<?=Url::toRoute(['/alfabank/alfabank/result'], true);?>" />
        <input type="hidden" name="LMI_FAILURE_URL" value="<?=Url::toRoute([$module->failUrl, 'id' => $orderModel->getId(), 'cash' => true], true);?>" />
        <input type="hidden" name="LMI_SUCCESS_URL" value="<?=Url::toRoute([$module->thanksUrl, 'id' => $orderModel->getId(), 'cash' => true], true);?>" />
        <?php if(!$autoSend) { ?>
            <input type="submit" value="Оплатить <?=$orderModel->getCost();?> <?=$module->currency;?>" />
        <?php } ?>
    </form>

<?php if($autoSend) { ?><script>document.getElementById('payment_alfabank_form').submit();</script><?php } ?>