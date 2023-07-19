# Class for working with djinni.co

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
```
