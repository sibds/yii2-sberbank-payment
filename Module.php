<?php
/**
 * Created by PhpStorm.
 * User: vadim
 * Date: 22.12.16
 * Time: 17:48
 */

namespace sibds\payment\sberbank;

use yii;

class Module extends \yii\base\Module
{
    public $gatewayUrl = 'https://securepayments.sberbank.ru/payment/rest/';
    public $gatewayTestUrl = 'https://3dsec.sberbank.ru/payment/rest/';
    public $testServer = false;
    public $adminRoles = ['admin', 'superadmin'];
    public $thanksUrl = '/main/spasibo-za-zakaz';
    public $failUrl = '/main/problema-s-oplatoy';
    public $currency = 'RUB';
    public $username = '';
    public $password = '';
    public $orderModel = 'dvizh\order\models\Order';
    public $getId = null;
    public $getModel = null;
    public $getDescription = null;
    public $sessionTimeout = null;// in seconds
    public $refundRate = 100; // percentage of refund
    public $logCategory = false;
    public $supportCart = false;
    public $taxSystem = 0;

    public function init()
    {
        parent::init();
    }

    public function gateway($method, $data) {
        if (!empty($this->logCategory)){
            yii::info("Request: " . ($this->testServer?$this->gatewayTestUrl:$this->gatewayUrl).$method . "\nData: " . json_encode($data) . "\n", $this->logCategory);
        }
        $curl = curl_init(); // Инициализируем запрос
        curl_setopt_array($curl, array(
            CURLOPT_URL => ($this->testServer?$this->gatewayTestUrl:$this->gatewayUrl).$method, // Полный адрес метода
            CURLOPT_VERBOSE => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true, // Возвращать ответ
            CURLOPT_POST => true, // Метод POST
            CURLOPT_POSTFIELDS => http_build_query($data) // Данные в запросе
        ));
        $response = curl_exec($curl); // Выполненяем запрос
        if (!empty($this->logCategory)){
            yii::info("Response: " . $response . "\n", $this->logCategory);
        }
        $response = json_decode($response, true); // Декодируем из JSON в массив
        curl_close($curl); // Закрываем соединение
        return $response; // Возвращаем ответ
    }
}
