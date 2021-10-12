<?php
namespace sibds\payment\sberbank\controllers;

use yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\base\Event;

class SberbankController extends Controller
{
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    function actionResult($order, $orderId)
    {
        $module = Yii::$app->controller->module;

        $data = array(
            'userName' => $module->username,
            'password' => $module->password,
            'orderId' => $orderId,
        );

        $response = $module->gateway('getOrderStatus.do', $data);

        if(isset($response['OrderStatus'])&&$response['OrderStatus']==2){
            $pmOrderId = (int)$response['OrderNumber'];
            $orderModel = $module->orderModel;
            $orderModel = is_null($module->getModel)?$orderModel::findOne($pmOrderId):(is_callable($module->getModel)?call_user_func($module->getModel, [$pmOrderId]):$orderModel::findOne($module->getModel));
            //$orderModel = $orderModel::findOne($pmOrderId);
            if (!$orderModel) {
                throw new NotFoundHttpException('The requested order does not exist.');
            }


            $orderModel->setPaymentStatus('yes');
            $orderModel->save(false);
            
            $event = new Event();
            $event->sender = $orderModel;
            Yii::$app->trigger('successPayment', $event);

            return $this->redirect($module->thanksUrl);
        }

        return $this->redirect($module->failUrl);
    }
}
