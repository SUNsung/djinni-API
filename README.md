# Class for working with djinni.co
### PHP8.2+ is required to work
`No third party libraries needed, only php`

Since the djinni.co resource does not have an API, a class was written to work with the resource from the server, in a developer-friendly format.

Login parameters can be set in the .conf.php file or directly in the methods

```php

//Первичный автозагрущик
require_once __DIR__."/bin/autoload.php";

/** @var \djinni\Start $DJ Обьект класса работы с djinni */
$DJ = new \djinni\Start(mail: sysConstants::$user_email, password: sysConstants::$user_pass);

//Выход из сессии
//$DJ->logout();

//Проверка авторизации и авторизация если нужно
if(!$DJ->is_auth()){
    $rez = $DJ->auth();
    if (!$rez) sys::print(code: 401, title: "Unauthorized");
}

$arr = [
    "archive" => $DJ->load_inbox(is_archive: true),  //получение сообщений из архива
    "inbox" => $DJ->load_inbox()   //ПОлучение сообщений из почтового ящика
];

sys::print($arr);
```





#### Message object example
```json
{
  "id": 15483452,
  "name": "PHP Backend Developer",
  "msg": "Ви відкрили контакти роботодавцю.",
  "date": "18 липня 2023 р.",
  "comments": false,
  "recruiter": "Tatiana Zalvovska, Senior Technical Recruiter",
  "company": {
    "id": 23599,
    "name": "Beliani",
    "img": "https://p.djinni.co/05/ee7b05b7a7966223a16a942b37cef1/1677054671213_400.jpg",
    "url": "https://djinni.co/jobs/?company=beliani-com-837ef",
    "is_top": false
  }
}
```
