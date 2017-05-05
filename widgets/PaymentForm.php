<?php
namespace sibds\payment\sberbank\widgets;

use sibds\payment\sberbank\Module;
use yii;
use yii\helpers\Url;

class PaymentForm extends \yii\base\Widget
{
    public $description = '';
    public $orderModel;
    public $autoSend = false;

    public function init()
    {
        return parent::init();
    }

    public function run()
    {
        if (empty($this->orderModel)) {
            return false;
        }

        /**
         * @var Module
         */
        $module = yii::$app->getModule('sberbank');

        $data = array(
            'userName' => $module->username,
            'password' => $module->password,
            'orderNumber' => urlencode($this->orderModel->getId()),
            'amount' => urlencode($this->orderModel->getCost() * 100), // передача данных в копейках/центах
            'returnUrl' => Url::toRoute(['/sberbank/sberbank/result', 'order' => urlencode($this->orderModel->getId())], true),
            //'failUrl' => Url::toRoute([$module->failUrl], true),
        );

        /**
         * ЗАПРОС РЕГИСТРАЦИИ ОДНОСТАДИЙНОГО ПЛАТЕЖА В ПЛАТЕЖНОМ ШЛЮЗЕ
         *        register.do
         *
         * ПАРАМЕТРЫ
         *        userName            Логин магазина.
         *        password            Пароль магазина.
         *        orderNumber            Уникальный идентификатор заказа в магазине.
         *        amount                Сумма заказа.
         *        returnUrl            Адрес, на который надо перенаправить пользователя в случае успешной оплаты.
         *
         * ОТВЕТ
         *        В случае ошибки:
         *            errorCode        Код ошибки. Список возможных значений приведен в таблице ниже.
         *            errorMessage    Описание ошибки.
         *
         *        В случае успешной регистрации:
         *            orderId            Номер заказа в платежной системе. Уникален в пределах системы.
         *            formUrl            URL платежной формы, на который надо перенаправить браузер клиента.
         *
         *    Код ошибки        Описание
         *        0            Обработка запроса прошла без системных ошибок.
         *        1            Заказ с таким номером уже зарегистрирован в системе.
         *        3            Неизвестная (запрещенная) валюта.
         *        4            Отсутствует обязательный параметр запроса.
         *        5            Ошибка значения параметра запроса.
         *        7            Системная ошибка.
         */
        $response = $module->gateway('register.do', $data);

        /**
         * ЗАПРОС РЕГИСТРАЦИИ ДВУХСТАДИЙНОГО ПЛАТЕЖА В ПЛАТЕЖНОМ ШЛЮЗЕ
         *        registerPreAuth.do
         *
         * Параметры и ответ точно такие же, как и в предыдущем методе.
         * Необходимо вызывать либо register.do, либо registerPreAuth.do.
         */
//	$response = $module->gateway('registerPreAuth.do', $data);

        if (isset($response['errorCode'])) { // В случае ошибки вывести ее
            header('Location: ' . Url::toRoute([$module->failUrl], true));
            //echo 'Ошибка #' . $response['errorCode'] . ': ' . $response['errorMessage'];
            die();
        } else { // В случае успеха перенаправить пользователя на плетжную форму
            $this->orderModel->order_info = $response['orderId'];
            $this->orderModel->save();

            header('Location: ' . $response['formUrl']);
            die();
        }
    }
}
