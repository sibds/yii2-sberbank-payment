<?php
namespace sibds\payment\sberbank\widgets;

use sibds\payment\sberbank\Module;
use yii;
use yii\helpers\Url;

class CheckstatusForm extends \yii\base\Widget
{
    public $orderId;
    public $autoSend = false;

    public function init()
    {
        return parent::init();
    }

    public function run()
    {
        if (empty($this->orderId)) {
            return false;
        }

        /**
         * @var Module
         */
        $module = yii::$app->getModule('sberbank');
        $data = array(
            'userName' => $module->username,
            'password' => $module->password,
            'orderId' => $this->orderId,
        );

        /**
         * РАСШИРЕННЫЙ ЗАПРОС СОСТОЯНИЯ ЗАКАЗА
         *      getOrderStatusExtended.do
         *
         * ПАРАМЕТРЫ
         *      userName       Логин магазина, полученный при подключении
         *      password       Пароль магазина, полученный при подключении
         *      orderId        Номер заказа в платежной системе. Уникален в пределах системы
         *      orderNumber    Номер (идентификатор) заказа в системе магазина
         *      language       Язык в кодировке ISO 639-1. Если не указан, считается, что язык – русский. Сообщение ошибке будет
         *                     возвращено именно на этом языке
         *  В запросе должен присутствовать либо orderId, либо orderNumber. Если в запросе присутствуют оба параметра, 
         *  то приоритетным считается orderId.
         *
         * ОТВЕТ
         *      orderNumber             Номер (идентификатор) заказа в системе магазина
         *      orderStatus             По значению этого параметра определяется состояние заказа в платёжной системе
         *      actionCode              Код ответа
         *      actionCodeDescription   Расшифровка кода ответа на языке, переданном в параметре Language в запросе
         *      errorCode               Код ошибки
         *      errorMessage            Описание ошибки на языке, переданном в параметре Language в запросе
         *      amount                  Сумма платежа в копейках (или центах)
         *      currency                Код валюты платежа ISO 4217. Если не указан, считается равным 643 (российские рубли)
         *      date                    Дата регистрации заказа
         *      orderDescription        Описание заказа, переданное при его регистрации
         *      ip                      IP-адрес покупателя
         *
         *  Возможные значения orderStatus
         *      0 - Заказ зарегистрирован, но не оплачен;
         *      1 - Предавторизованная сумма захолдирована (для двухстадийных платежей);
         *      2 - Проведена полная авторизация суммы заказа;
         *      3 - Авторизация отменена;
         *      4 - По транзакции была проведена операция возврата;
         *      5 - Инициирована авторизация через ACS банка-эмитента;
         *      6 - Авторизация отклонена.
         *  
         *  Возможные значнеия errorCode
         *      0 - Обработка запроса прошла без системных ошибок;
         *      1 - Ожидается [orderId] или [orderNumber];
         *      5 - Доступ запрещён;
         *      5 - Пользователь должен сменить свой пароль;
         *      6 - Заказ не найден;
         *      7 - Системная ошибка.
         */
        $response = $module->gateway('getOrderStatusExtended.do', $data);

        if (isset($response['errorCode']) && ($response['errorCode'] === "0")) {
            return $response;
        } else {
            return False;
        }
    }
}
