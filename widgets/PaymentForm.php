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
    public $cart = false;

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

        $id = is_null($module->getId)?$this->orderModel->getId():(is_callable($module->getId)?call_user_func($module->getId, [$this->orderModel]):$module->getId);
        $data = array(
            'userName' => $module->username,
            'password' => $module->password,
            'orderNumber' => urlencode($id),
            'amount' => urlencode($this->orderModel->getCost() * 100), // передача данных в копейках/центах
            'returnUrl' => Url::toRoute(['/sberbank/sberbank/result', 'order' => urlencode($id)], true),            
            //'failUrl' => Url::toRoute([$module->failUrl], true),
        );

        if(!is_null($module->sessionTimeout)){
            $data['sessionTimeoutSecs'] = $module->sessionTimeout;
        }

        if($module->supportCart&&$this->cart){
            $data['orderBundle'] = [];
            if($this->orderModel->email!=''){
                $data['orderBundle']['customerDetails']['email'] = $this->orderModel->email;                
            }elseif ($this->orderModel->phone!='') {             
                $data['orderBundle']['customerDetails']['phone'] = $this->orderModel->phone;              
            }

            $data['orderBundle']['cartItems']['items'] = $this->cart;
            
        }
        
        if(!is_null($module->getDescription)){
            if(is_callable($module->getDescription)){
                $data['description'] = call_user_func($module->getDescription, [$this->orderModel]);
            } else{
                $data['description'] = $module->getDescription;
            }
        }


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
