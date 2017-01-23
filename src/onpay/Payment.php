<?php

namespace ejen\payment\onpay;

use yii\helpers\Url;

class Payment extends \yii\base\Model
{
    public $baseUrl = 'http://secure.onpay.ru';

    public $secret_key;

    public $username;

    /**
     * Режим платежа.
     * free - пользователь сможет менять сумму платежа в платежной форме
     * fix - пользователю будет показана сумма к зачислению(т.е. за вычетом комиссий) без возможности её редактирования.
     * Обязательно указать сумму платежа price и назначение pay_for
     */
    public $pay_mode = 'fix';

    /**
     * Сумма платежа
     * Дробное число, до 2-х знаков после запятой
     */
    public $price;

    /**
     * Основная валюта ценника.
     * Трехсимвольное наименование валюты
     */
    public $ticker = 'RUR';

    /**
     * Номер заказа, заявки, аккаунт пользователя и т.п. для идентификации платежа в системе магазина.
     */
    public $pay_for;

    /**
     * Принудительная конвертация в валюту ценника. Если включена - все поступающие платежи будут конвертироваться в валюту ценника.
     */
    public $convert = true;

    /**
     * Ссылка на которую будет переадресован пользователь после успешного завершения платежа.
     */
    public $url_success;

    /**
     * Ссылка на которую будет переадресован пользователь после неудачного завершения платежа.
     */
    public $url_fail;

    public $user_email;
    public $user_phone;
    public $note;
    public $ln = 'ru';
    public $f = 8;
    public $one_way;
    public $price_final = true;

    public function rules()
    {
        return [
            [['username', 'pay_mode', 'price', 'ticker', 'pay_for', 'convert', 'url_success', 'url_fail'], 'required'],
            [['pay_mode'], 'in', 'range' => ['fix', 'free']],
            [['ticker'], 'in', 'range' => ['RUR', 'USD', 'TST']],
            [['pay_for'], 'string', 'max' => '100'],
            [['url_success', 'url_fail'], 'string', 'max' => 255],
            [['convert'], 'boolean'],
            ['price', 'number'],
            ['price', 'filter', 'filter' => function($value) {
                $value = round(floatval($value), 2);
                $value = sprintf('%01.2f', $value);
        
                if (substr($value, -1) == '0')
                {
                    $value = sprintf('%01.1f', $value);
                }
                return $value;
            }],

        ];
    }

    public function getUrl()
    {
        if (!$this->validate()) return false;

        $convert = $this->convert ? 'yes' : 'no';

        $md5 = strtoupper(md5("{$this->pay_mode};{$this->price};{$this->ticker};{$this->pay_for};{$convert};{$this->secret_key}"));
        
        $params = [
            'pay_mode' => $this->pay_mode,
            'pay_for' => $this->pay_for,
            'price' => $this->price,
            'ticker' => $this->ticker,
            'convert' => $convert,
            'md5' => $md5,
            'user_email' => urlencode($this->user_email),
            'f' => $this->f,
            'ln' => $this->ln,
            'url_success_enc' => urlencode(is_array($this->url_success) ? Url::to($this->url_success) : $this->url_success),
            'url_fail_enc' => urlencode(is_array($this->url_fail) ? Url::to($this->url_fail) : $this->url_fail),
        ];

        return $this->baseUrl.'/pay/'.$this->username.'?'.http_build_query($params);
    }
}