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

    public function init()
    {
        parent::init();
    }

    public function gateway($method, $data) {
        $curl = curl_init(); // Инициализируем запрос
        curl_setopt_array($curl, array(
            CURLOPT_URL => ($this->testServer?$this->gatewayTestUrl:$this->gatewayUrl).$method, // Полный адрес метода
            CURLOPT_RETURNTRANSFER => true, // Возвращать ответ
            CURLOPT_POST => true, // Метод POST
            CURLOPT_POSTFIELDS => http_build_query($data) // Данные в запросе
        ));
        $response = curl_exec($curl); // Выполненяем запрос

        $response = json_decode($response, true); // Декодируем из JSON в массив
        curl_close($curl); // Закрываем соединение
        return $response; // Возвращаем ответ
    }
}
