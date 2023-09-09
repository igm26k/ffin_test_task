# ffin_test_task

## Системные требования

**Linux ОС с установленными:**
- Docker
- Docker-compose
- Make
- Git

## Установка

1. Клонируйте проект в вашу_локальную_директорию
2. Перейдите в вашу_локальную_директорию
3. Запустите `make`

    **Пример:**
    ```shell
    git clone git@github.com:igm26k/ffin_test_task.git && \
    cd ffin_test_task && \
    make
    ```

4. После инициализации вы должны увидеть успешное прохождение тестов и список запущенных контейнеров

    **Пример:**
    ```shell
   OK (10 tests, 57 assertions)
   
    CONTAINER ID   IMAGE                     COMMAND                  CREATED          STATUS          PORTS                                                                                                                                                 NAMES
    9e19dd144fdc   ffin_test_task_cbr        "docker-entrypoint.sh"   52 minutes ago   Up 52 minutes   9000/tcp                                                                                                                                              cbr
    d7a2c106cb64   memcached                 "docker-entrypoint.s…"   53 minutes ago   Up 53 minutes   11211/tcp                                                                                                                                             cbr_memcached
    8122e819bdd4   rabbitmq:3.9-management   "docker-entrypoint.s…"   53 minutes ago   Up 53 minutes   4369/tcp, 5671/tcp, 0.0.0.0:5672->5672/tcp, :::5672->5672/tcp, 15671/tcp, 15691-15692/tcp, 25672/tcp, 0.0.0.0:15672->15672/tcp, :::15672->15672/tcp   cbr_rabbitmq
    ```

## Использование

Все команды можно запускать из контейнера `cbr` при помощи команды `bin/console`, либо из хостовой системы при помощи
`bin/dconsole`, которая выполняет `bin/console` внутри контейнера.

Ниже показаны примеры использования `bin/dconsole`.

### Получение курсов, кроскурсов ЦБ заданной пары валют за указанную дату

```shell
$ bin/dconsole app:get-currency-rate --help
Description:
  Получить курс указанной валюты на заданный день

Usage:
  app:get-currency-rate [options]

Options:
  -d, --date=DATE                            Дата в любом валидном формате
  -c, --currencyCode=CURRENCYCODE            Код валюты
  -b, --baseCurrencyCode[=BASECURRENCYCODE]  Код базовой валюты [default: "RUB"]
```

**Пример:**
```shell
$ bin/dconsole app:get-currency-rate -d 05.09.2023 -c USD -b JPY
                                                                                              
 [OK] The JPY/USD currency pair exchange rate for 05.09.2023 is 0.00683. The difference with the previous trading day
      -0.526%.
```

### Сбор данных с cbr за 180 предыдущих дней
```shell
$ bin/dconsole app:get-currencies --help
Description:
  Запустить сбор данных с cbr за 180 предыдущих дней

Usage:
  app:get-currencies
```

1. Сначала необходимо запустить обработчик сообщений в брокере:

   ```shell
   bin/dconsole messenger:consume async -vv
   ```
   
   Пример:
   ```shell
   $ ./bin/dconsole messenger:consume async_tasks -vv

   [OK] Consuming messages from transport "async_tasks".
   
   // The worker will automatically exit once it has received a stop signal via   
   // the messenger:stop-workers command.
   
   // Quit the worker with CONTROL-C.
   ```

2. После запуска откройте еще одно окно терминала и запустите команду:

   ```shell
   bin/dconsole app:get-currencies
   ```

   **Пример:**
   ```shell
   $ bin/dconsole app:get-currencies

   [OK] Created 180 CurrencyRateTask. You can see the execution log in            
        ./var/log/dev-2023-09-08.log
   ```

3. Посмотреть ход выполнения обработки сообщений можно в файле `./var/log/{env}-{date}.log`
   
   **Пример:**
   ```shell
   $ tail -f -n 5 ./var/log/dev-2023-09-08.log
   [2023-09-08T07:13:53.971155+00:00] messenger.INFO: Received message App\Message\CurrencyRateTask {"class":"App\\Message\\CurrencyRateTask"} []
   [2023-09-08T07:17:24.089110+00:00] app.INFO: Start getCursOnDate 2023-04-01 [] []
   [2023-09-08T07:17:24.154830+00:00] app.INFO: getCursOnDate 2023-04-01 response {"ValuteData":{"stdClass":{"ValuteCursOnDate":[{"Vname":"Австралийский доллар                                                                                                                                                                                                                                          ","Vnom":"1","Vcurs":"51.8994","Vcode":"36","VchCode":"AUD","VunitRate":"51.8994"},{"Vname":"Азербайджанский манат                                                                                                                                                                                                                                         ","Vnom":"1","Vcurs":"45.4843","Vcode":"944","VchCode":"AZN","VunitRate":"45.4843"},{"Vname":"Фунт стерлингов Соединенного королевства                                                                                                                                                                                                                      ","Vnom":"1","Vcurs":"95.7262","Vcode":"826","VchCode":"GBP","VunitRate":"95.7262"},{"Vname":"Армянский драм                                                                                                                                                                                                                                                ","Vnom":"100","Vcurs":"19.9041","Vcode":"51","VchCode":"AMD","VunitRate":"0.199041"},{"Vname":"Белорусский рубль                                                                                                                                                                                                                                             ","Vnom":"1","Vcurs":"27.0949","Vcode":"933","VchCode":"BYN","VunitRate":"27.0949"},{"Vname":"Болгарский лев                                                                                                                                                                                                                                                ","Vnom":"1","Vcurs":"43.0363","Vcode":"975","VchCode":"BGN","VunitRate":"43.0363"},{"Vname":"Бразильский реал                                                                                                                                                                                                                                              ","Vnom":"1","Vcurs":"15.0872","Vcode":"986","VchCode":"BRL","VunitRate":"15.0872"},{"Vname":"Венгерский форинт                                                                                                                                                                                                                                             ","Vnom":"100","Vcurs":"22.1018","Vcode":"348","VchCode":"HUF","VunitRate":"0.221018"},{"Vname":"Вьетнамский донг                                                                                                                                                                                                                                              ","Vnom":"10000","Vcurs":"32.7641","Vcode":"704","VchCode":"VND","VunitRate":"0.00327641"},{"Vname":"Гонконгский доллар                                                                                                                                                                                                                                            ","Vnom":"10","Vcurs":"98.6707","Vcode":"344","VchCode":"HKD","VunitRate":"9.86707"},{"Vname":"Грузинский лари                                                                                                                                                                                                                                               ","Vnom":"1","Vcurs":"30.1997","Vcode":"981","VchCode":"GEL","VunitRate":"30.1997"},{"Vname":"Датская крона                                                                                                                                                                                                                                                 ","Vnom":"1","Vcurs":"11.2996","Vcode":"208","VchCode":"DKK","VunitRate":"11.2996"},{"Vname":"Дирхам ОАЭ                                                                                                                                                                                                                                                    ","Vnom":"1","Vcurs":"21.0530","Vcode":"784","VchCode":"AED","VunitRate":"21.053"},{"Vname":"Доллар США                                                                                                                                                                                                                                                    ","Vnom":"1","Vcurs":"77.3233","Vcode":"840","VchCode":"USD","VunitRate":"77.3233"},{"Vname":"Евро                                                                                                                                                                                                                                                          ","Vnom":"1","Vcurs":"84.1116","Vcode":"978","VchCode":"EUR","VunitRate":"84.1116"},{"Vname":"Египетский фунт                                                                                                                                                                                                                                               ","Vnom":"10","Vcurs":"25.0251","Vcode":"818","VchCode":"EGP","VunitRate":"2.50251"},{"Vname":"Индийская рупия                                                                                                                                                                                                                                               ","Vnom":"100","Vcurs":"94.0240","Vcode":"356","VchCode":"INR","VunitRate":"0.94024"},{"Vname":"Индонезийская рупия                                                                                                                                                                                                                                           ","Vnom":"10000","Vcurs":"51.3367","Vcode":"360","VchCode":"IDR","VunitRate":"0.00513367"},{"Vname":"Казахстанский тенге                                                                                                                                                                                                                                           ","Vnom":"100","Vcurs":"17.2577","Vcode":"398","VchCode":"KZT","VunitRate":"0.172577"},{"Vname":"Канадский доллар                                                                                                                                                                                                                                              ","Vnom":"1","Vcurs":"57.1369","Vcode":"124","VchCode":"CAD","VunitRate":"57.1369"},{"Vname":"Катарский риал                                                                                                                                                                                                                                                ","Vnom":"1","Vcurs":"21.2427","Vcode":"634","VchCode":"QAR","VunitRate":"21.2427"},{"Vname":"Киргизский сом                                                                                                                                                                                                                                                ","Vnom":"100","Vcurs":"88.4504","Vcode":"417","VchCode":"KGS","VunitRate":"0.884504"},{"Vname":"Китайский юань                                                                                                                                                                                                                                                ","Vnom":"1","Vcurs":"11.2411","Vcode":"156","VchCode":"CNY","VunitRate":"11.2411"},{"Vname":"Молдавский лей                                                                                                                                                                                                                                                ","Vnom":"10","Vcurs":"42.0103","Vcode":"498","VchCode":"MDL","VunitRate":"4.2010300000000012"},{"Vname":"Новозеландский доллар                                                                                                                                                                                                                                         ","Vnom":"1","Vcurs":"48.5204","Vcode":"554","VchCode":"NZD","VunitRate":"48.5204"},{"Vname":"Норвежская крона                                                                                                                                                                                                                                              ","Vnom":"10","Vcurs":"73.8572","Vcode":"578","VchCode":"NOK","VunitRate":"7.385720000000001"},{"Vname":"Польский злотый                                                                                                                                                                                                                                               ","Vnom":"1","Vcurs":"18.0060","Vcode":"985","VchCode":"PLN","VunitRate":"18.006"},{"Vname":"Румынский лей                                                                                                                                                                                                                                                 ","Vnom":"1","Vcurs":"17.0080","Vcode":"946","VchCode":"RON","VunitRate":"17.008"},{"Vname":"СДР (специальные права заимствования)                                                                                                                                                                                                                         ","Vnom":"1","Vcurs":"104.0540","Vcode":"960","VchCode":"XDR","VunitRate":"104.054"},{"Vname":"Сингапурский доллар                                                                                                                                                                                                                                           ","Vnom":"1","Vcurs":"58.2693","Vcode":"702","VchCode":"SGD","VunitRate":"58.2693"},{"Vname":"Таджикский сомони                                                                                                                                                                                                                                             ","Vnom":"10","Vcurs":"70.8504","Vcode":"972","VchCode":"TJS","VunitRate":"7.0850399999999993"},{"Vname":"Таиландский бат                                                                                                                                                                                                                                               ","Vnom":"10","Vcurs":"22.6922","Vcode":"764","VchCode":"THB","VunitRate":"2.26922"},{"Vname":"Турецкая лира                                                                                                                                                                                                                                                 ","Vnom":"10","Vcurs":"40.3499","Vcode":"949","VchCode":"TRY","VunitRate":"4.03499"},{"Vname":"Новый туркменский манат                                                                                                                                                                                                                                       ","Vnom":"1","Vcurs":"22.0924","Vcode":"934","VchCode":"TMT","VunitRate":"22.0924"},{"Vname":"Узбекский сум                                                                                                                                                                                                                                                 ","Vnom":"10000","Vcurs":"67.6304","Vcode":"860","VchCode":"UZS","VunitRate":"0.00676304"},{"Vname":"Украинская гривна                                                                                                                                                                                                                                             ","Vnom":"10","Vcurs":"20.9349","Vcode":"980","VchCode":"UAH","VunitRate":"2.09349"},{"Vname":"Чешская крона                                                                                                                                                                                                                                                 ","Vnom":"10","Vcurs":"35.7432","Vcode":"203","VchCode":"CZK","VunitRate":"3.57432"},{"Vname":"Шведская крона                                                                                                                                                                                                                                                ","Vnom":"10","Vcurs":"74.6804","Vcode":"752","VchCode":"SEK","VunitRate":"7.46804"},{"Vname":"Швейцарский франк                                                                                                                                                                                                                                             ","Vnom":"1","Vcurs":"84.3864","Vcode":"756","VchCode":"CHF","VunitRate":"84.3864"},{"Vname":"Сербский динар                                                                                                                                                                                                                                                ","Vnom":"100","Vcurs":"71.8891","Vcode":"941","VchCode":"RSD","VunitRate":"0.718891"},{"Vname":"Южноафриканский рэнд                                                                                                                                                                                                                                          ","Vnom":"10","Vcurs":"43.4062","Vcode":"710","VchCode":"ZAR","VunitRate":"4.34062"},{"Vname":"Вон Республики Корея                                                                                                                                                                                                                                          ","Vnom":"1000","Vcurs":"59.3927","Vcode":"410","VchCode":"KRW","VunitRate":"0.0593927"},{"Vname":"Японская иена                                                                                                                                                                                                                                                 ","Vnom":"100","Vcurs":"57.9288","Vcode":"392","VchCode":"JPY","VunitRate":"0.579288"}]}}} []
   [2023-09-08T07:13:53.971250+00:00] messenger.INFO: Message App\Message\CurrencyRateTask handled by App\MessageHandler\CurrencyRateTaskHandler::__invoke {"class":"App\\Message\\CurrencyRateTask","handler":"App\\MessageHandler\\CurrencyRateTaskHandler::__invoke"} []
   [2023-09-08T07:13:53.971267+00:00] messenger.INFO: App\Message\CurrencyRateTask was handled successfully (acknowledging to transport). {"class":"App\\Message\\CurrencyRateTask"} []
   ```
   
_Полученные данные сохраняются в перманентный кеш. (При необходимости можно сделать сохранение в БД, 
но не стал на это тратить время)_

### Очистка кеша memcached

```shell
$ bin/dconsole app:cache-clear --help
Description:
  Очистить кеш memcached

Usage:
  app:cache-clear
```