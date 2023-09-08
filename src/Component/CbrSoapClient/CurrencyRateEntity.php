<?php

namespace App\Component\CbrSoapClient;

class CurrencyRateEntity
{
    public string $Vname;     // Название валюты
    public float  $Vnom;      // Номинал
    public float  $Vcurs;     // Курс
    public int    $Vcode;     // ISO Цифровой код валюты
    public string $VchCode;   // ISO Символьный код валюты
    public float  $VunitRate; // Курс за 1 единицу валюты
}