<?php
namespace sibds\payment\sberbank\widgets;

use sibds\payment\sberbank\Module;
use yii;
use yii\helpers\Url;

class ReturnForm extends \yii\base\Widget
{
    public $description = '';
    public $orderModel;
    public $autoSend = false;

    public function init()
    {
        return parent::init();
    }

    /**
     * ВОЗВРАТ СРЕДСТВ ОДНОСТАДИЙНОГО ПЛАТЕЖА В ПЛАТЕЖНОМ ШЛЮЗЕ
     *        refund.do
     *
     * ПАРАМЕТРЫ
     *        Название      Тип         Обязательно             Описание
     *        userName      AN..30          да          Логин магазина, полученный при подключении
     *        password      AN..30          да          Пароль магазина, полученный при подключении
     *        orderId       ANS36           да          Номер заказа в платежной системе. Уникален в пределах системы.
     *        amount        N..20           да          Сумма платежа в копейках (или центах)
     *
     * ПАРАМЕТРЫ ОТВЕТА
     *        Название      Тип         Обязательно             Описание
     *      errorCode     N3              Нет             Код ошибки.
     *      errorMessage  AN..512         Нет             Описание ошибки на языке.
     *
     *    Классификация:
     *       Значение               Описание
     *          0           Обработка запроса прошла без системных ошибок
     *          5           Ошибка значение параметра запроса
     *          6           Незарегистрированный OrderId
     *          7           Системная ошибка
     *    
     *    Расшифровка:
     *      Значение                Описание
     *          0           Обработка запроса прошла без системных ошибок
     *          5           Доступ запрещён
     *          5           Пользователь должен сменить свой пароль
     *          5           [orderId] не задан
     *          6           Неверный номер заказа
     *          7           Платёж должен быть в корректном состоянии
     *          7           Неверная сумма депозита (менее одного рубля)
     *          7           Ошибка системы
     */
    public function run()
    {
        if (empty($this->orderModel)) {
            return false;
        }

        /**
         * @var Module
         */
        $module = yii::$app->getModule('sberbank');

        $data = 
            array(
                'userName' => $module->username,
                'password' => $module->password,
                'orderId' => urlencode($this->orderModel->order_info),
                'amount' => urlencode($this->orderModel->getCost() * 100) // передача суммы в копейках
            );

        $response = $module->gateway('refund.do', $data);

        if (isset($response['errorCode']) && ($response['errorCode'] === "0")) {
            //echo 'Успех';
            return True;
        } else {
            //echo 'Ошибка #' . $response['errorCode'] . ': ' . $response['errorMessage'];
            return False;
        }
    }
}
