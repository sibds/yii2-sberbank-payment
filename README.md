Yii2 Sberbank Payment
=====================
Payment widget for sberbank

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist sibds/yii2-sberbank-payment "*"
```

or add

```
"sibds/yii2-sberbank-payment": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= \sibds\payment\sberbank\AutoloadExample::widget(); ?>
```

Support for events about the successful payment. Add the settings app.:
```php
// The event of successful payment
'on successPayment' => ['\frontend\controllers\ShopController', 'successPayment'],
```
