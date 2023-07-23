# Класс для роботи з djinni.co

### Потребуе PHP8.2+ для роботи

`Класс не вимагае ніяких сторонніх бібліотек, лише підключення файлу Start.php`

Так як з'явилася потреба в серверному рішені роботи з сайтом, а djinni.co не мае ніякого API - був написанний данний класс.

Весь функціонал написан тільки і тільки на php. **Класс не призначений для массового вивантаження чи для автоматичної россилки резюме.**

---

**Отримування вакансій**, **пошук вакансій** та завантаження **дерева параметрів пошуку** не вимагае авторизаціі.

Читання **листів** та **останніх відвідувачів** потребуе авторізаціі. Реалізована авторизація через email+pass.

.

### Ініціалізація

Ініціалізація потребуе мейлу та паролю від акаунту
```php
new \djinni\Start(mail: $user_email, password: $user_pass);
```

Можна ініціалюзувати без авторизаціі
```php
new \djinni\Start();
```

.

### Параметри пошуку

Для отримання актуальних параметрів пошуку е метод `load_jobsFilter()`

.

### Пошук

Для пошуку ініалізувати метод `start_search(bool $all_page, int $pages)`
Необв'язкові параметри:
- `all_page`: вигружати усі сторінки при пошуку чи ні  (за замовчуванням **false**)
- `pages`: Скільки сторінок вигрузити. Тільки якщо **all_page=false**  (за замовчуванням **1**)

.

Пошуковий об'ект мае такі методи для встановлення праметрів:
- `page(int $page):self`
- `add_specialization(string $param):self`
- `add_country(string $param):self`
- `add_experience(string $param):self`
- `add_employment(string $param):self`
- `add_companyType(string $param):self`
- `add_salaryFrom(string $param):self`
- `add_english(string $param):self`
- `add_others(string $param):self`
- `fulltext(bool $status):self`
- `titleOnly(bool $status):self`
- `anyOfKeywords(string $string):self`
- `excludeKeywords(string $string):self`

Пошуковий об'ект мае такі методи для отримання данних:
- `get_page():int`
- `get_url():string`

.

Приклади встановлення пошукових пармерів:

```php
$DJ->start_search()->add_specialization("PHP")->add_employment("remote");
```
```php
$DJ->start_search()->page(2);
```
```php
$SS = $DJ->start_search();
$SS->add_english("no_english")->add_english("basic")->add_english("pre");
$SS->add_specialization("PHP");
$SS->add_salaryFrom("2500");
$SS->add_employment("remote");
```

---

#### Приклад об'екту повідомлення

```json
{
  "id": 15483452,
  "name": "PHP Backend Developer",
  "msg": "Ви відкрили контакти роботодавцю.",
  "date": "18 липня 2023 р.",
  "comments": false,
  "recruiter": {
    "name":"Tatiana Zalvovska",
    "type":"Senior Technical Recruiter"
  },
  "company": {
    "id": 23599,
    "paramId": "beliani-com-837ef",
    "name": "Beliani",
    "img": "https://p.djinni.co/05/ee7b05b7a7966223a16a942b37cef1/1677054671213_400.jpg",
    "url": "https://djinni.co/jobs/?company=beliani-com-837ef",
    "is_top": false
  }
}
```

---

#### Приклад об'екту вакансії

```json
{
  "id": "63cae7fa02677af17d2424c22818a248463d3e44",
  "view": 32,
  "fill": 2,
  "date": "21 липня",
  "job": {
    "name": "PHP Developer",
    "url": "https://djinni.co/jobs/445813-php-developer/",
    "salary": "від $2000",
    "description": [
      "Обов'язки:",
      "- Підтримка та доопрацювання функціонування самописної (PHP-фреймоворк Yii1,Yii2) системи;",
      "- розробка внутрішніх проектів на Yii1, YII2;",
      "- Інтеграція з різними сервісами за допомогою API;",
      "- Розробка та підтримка власних API;",
      "- участь в оптимізації системи;",
      "Вимоги:",
      "- Досвід роботи з PHP від 4 років, PHP 5.6 – 7.4;",
      "- Знання PHP, MySQL, MongoDB, JS/JQuery;",
      "- досвід оптимізації SQL запитів;",
      "- досвід роботи з фреймворком Yii1, Yii2;",
      "- досвід роботи з GIT;",
      "- Знання Linux (на просунутому рівні)",
      "- досвід роботи з Amazon AWS (EC2, S3) дуже бажано та інші сервіси;",
      "- навики базової верстки;",
      "- вміння підняти власний модуль, вникати в чужий код та доопрацьовувати його;",
      "- Вміння читати документацію сторонніх сервісів та за необхідності інтегрувати їх.",
      "- вміння знаходити швидкі та оптимальні рішення, старанність, бажання створювати закінчений продукт, відповідальність, самостійність та дисциплінованість;",
      "- Вміння розуміти завдання без докладного технічного завдання, у якому необхідно вказувати кожну очевидну деталь."
    ]
  },
  "location": "Україна (Київ)",
  "tags": [
    "Product",
    "Office/Remote на ваш вибір",
    "5 років досвіду",
    "Beginner/Elementary"
  ],
  "company": {
    "paramId": "maestro-ticket-system-f960f",
    "name": "Maestro Ticket System (Kontramarka Україна)",
    "img": "https://p.djinni.co/a9/d894a16492b5f4086be9fb8f9978be/1519906436128_400.jpg",
    "url": "https://djinni.co/jobs/?company=maestro-ticket-system-f960f"
  },
  "recruiter": {
    "name": "Александр Порядченко",
    "type": "CEO",
    "url": "https://djinni.co/r/10994-ceo-at-maestro-ticket-system/"
  }
}
```

---



#### Приклад об'екту дерева параметрів пошуку

```json
{
  "specialization": {
    "name": "Спеціалізація",
    "values": [
      {
        "name": "JavaScript / Front-End",
        "key": "JavaScript"
      },
      {
        "name": "Java",
        "key": "Java"
      },
      {
        "name": "C# / .NET",
        "key": ".NET"
      },
      {
        "name": "Python",
        "key": "Python"
      },
      {
        "name": "PHP",
        "key": "PHP"
      },
      {
        "name": "Node.js",
        "key": "Node.js"
      },
      {
        "name": "iOS",
        "key": "iOS"
      },
      {
        "name": "Android",
        "key": "Android"
      },
      {
        "name": "C / C++ / Embedded",
        "key": "C++"
      },
      {
        "name": "Flutter",
        "key": "Flutter"
      },
      {
        "name": "Golang",
        "key": "Golang"
      },
      {
        "name": "Ruby",
        "key": "Ruby"
      },
      {
        "name": "Scala",
        "key": "Scala"
      },
      {
        "name": "Salesforce",
        "key": "Salesforce"
      },
      {
        "name": "Rust",
        "key": "Rust"
      },
      {
        "name": "QA Manual",
        "key": "QA"
      },
      {
        "name": "QA Automation",
        "key": "QA Automation"
      },
      {
        "name": "Design / UI/UX",
        "key": "Design"
      },
      {
        "name": "2D/3D Artist / Illustrator",
        "key": "Artist"
      },
      {
        "name": "Project Manager",
        "key": "Project Manager"
      },
      {
        "name": "Product Manager",
        "key": "Product Manager"
      },
      {
        "name": "Architect / CTO",
        "key": "Lead"
      },
      {
        "name": "DevOps",
        "key": "DevOps"
      },
      {
        "name": "Business Analyst",
        "key": "Business Analyst"
      },
      {
        "name": "Data Science",
        "key": "Data Science"
      },
      {
        "name": "Data Analyst",
        "key": "Data Analyst"
      },
      {
        "name": "Sysadmin",
        "key": "Sysadmin"
      },
      {
        "name": "Gamedev / Unity",
        "key": "Unity"
      },
      {
        "name": "SQL / DBA",
        "key": "SQL"
      },
      {
        "name": "Security",
        "key": "Security"
      },
      {
        "name": "Data Engineer",
        "key": "Data Engineer"
      },
      {
        "name": "Scrum Master / Agile Coach",
        "key": "Scrum Master"
      },
      {
        "name": "Marketing",
        "key": "Marketing"
      },
      {
        "name": "HR",
        "key": "HR"
      },
      {
        "name": "Recruiter",
        "key": "Recruiter"
      },
      {
        "name": "Customer/Technical Support",
        "key": "Support"
      },
      {
        "name": "Sales",
        "key": "Sales"
      },
      {
        "name": "SEO",
        "key": "SEO"
      },
      {
        "name": "Technical Writing",
        "key": "Technical Writing"
      },
      {
        "name": "Lead Generation",
        "key": "Lead Generation"
      },
      {
        "name": "(Other)",
        "key": "Other"
      }
    ]
  },
  "country": {
    "name": "Країна",
    "values": [
      {
        "name": "Україна",
        "key": "UKR"
      },
      {
        "name": "Польща",
        "key": "POL"
      },
      {
        "name": "Німеччина",
        "key": "DEU"
      },
      {
        "name": "Іспанія",
        "key": "ESP"
      },
      {
        "name": "Португалія",
        "key": "PRT"
      },
      {
        "name": "Азербайджан",
        "key": "AZE"
      },
      {
        "name": "Країни ЄС",
        "key": "eu"
      },
      {
        "name": "Інші країни",
        "key": "other"
      }
    ]
  },
  "city": {
    "name": "Місто",
    "values": [
      {
        "name": "Київ",
        "key": "kyiv"
      },
      {
        "name": "Вінниця",
        "key": "vinnytsia"
      },
      {
        "name": "Дніпро",
        "key": "dnipro"
      },
      {
        "name": "Івано-Франківськ",
        "key": "ivano-frankivsk"
      },
      {
        "name": "Житомир",
        "key": "zhytomyr"
      },
      {
        "name": "Запоріжжя",
        "key": "zaporizhzhia"
      },
      {
        "name": "Львів",
        "key": "lviv"
      },
      {
        "name": "Миколаїв",
        "key": "mykolaiv"
      },
      {
        "name": "Одеса",
        "key": "odesa"
      },
      {
        "name": "Тернопіль",
        "key": "ternopil"
      },
      {
        "name": "Харків",
        "key": "kharkiv"
      },
      {
        "name": "Хмельницький",
        "key": "khmelnytskyi"
      },
      {
        "name": "Черкаси",
        "key": "cherkasy"
      },
      {
        "name": "Чернігів",
        "key": "chernihiv"
      },
      {
        "name": "Чернівці",
        "key": "chernivtsi"
      },
      {
        "name": "Ужгород",
        "key": "uzhhorod"
      }
    ]
  },
  "experience": {
    "name": "Досвід роботи",
    "values": [
      {
        "name": "Без досвіду",
        "key": "no_exp"
      },
      {
        "name": "1 рік",
        "key": "1y"
      },
      {
        "name": "2 роки",
        "key": "2y"
      },
      {
        "name": "3 роки",
        "key": "3y"
      },
      {
        "name": "5 років",
        "key": "5y"
      }
    ]
  },
  "employment": {
    "name": "Зайнятість",
    "values": [
      {
        "name": "Віддалена робота",
        "key": "remote"
      },
      {
        "name": "Part-time",
        "key": "parttime"
      },
      {
        "name": "Офіс",
        "key": "office"
      }
    ]
  },
  "companyType": {
    "name": "Тип компанії",
    "values": [
      {
        "name": "Product",
        "key": "product"
      },
      {
        "name": "Outsource",
        "key": "outsource"
      },
      {
        "name": "Outstaff",
        "key": "outstaff"
      },
      {
        "name": "Agency",
        "key": "agency"
      }
    ]
  },
  "salaryFrom": {
    "name": "Зарплата від",
    "values": [
      {
        "name": "$1500",
        "key": "1500"
      },
      {
        "name": "$2500",
        "key": "2500"
      },
      {
        "name": "$3500",
        "key": "3500"
      },
      {
        "name": "$4500",
        "key": "4500"
      },
      {
        "name": "$5500",
        "key": "5500"
      },
      {
        "name": "$6500",
        "key": "6500"
      },
      {
        "name": "$7500",
        "key": "7500"
      },
      {
        "name": "$8500",
        "key": "8500"
      }
    ]
  },
  "english": {
    "name": "Англійська",
    "values": [
      {
        "name": "No English",
        "key": "no_english"
      },
      {
        "name": "Beginner/Elementary",
        "key": "basic"
      },
      {
        "name": "Pre-Intermediate",
        "key": "pre"
      },
      {
        "name": "Intermediate",
        "key": "intermediate"
      },
      {
        "name": "Upper-Intermediate",
        "key": "upper"
      },
      {
        "name": "Advanced/Fluent",
        "key": "fluent"
      }
    ]
  },
  "others": {
    "name": "Добірки вакансій",
    "values": [
      {
        "name": "Вказана зарплатна вилка",
        "key": "has_public_salary"
      },
      {
        "name": "Релокейт",
        "key": "relocate"
      },
      {
        "name": "Компенсація вартості релокейту",
        "key": "relocate_help"
      },
      {
        "name": "Український продукт 🇺🇦",
        "key": "ukrainian_product"
      },
      {
        "name": "MilTech 🪖",
        "key": "miltech"
      }
    ]
  }
}
```

---

```php
require_once __DIR__."/bin/autoload.php";

/** @var \djinni\Start $DJ класс djinni */
$DJ = new \djinni\Start(mail: sysConstants::$user_email, password: sysConstants::$user_pass);

//Вихід з сессіі
//$DJ->logout();

//Перевірка на авторизацію і авторизація якщо потрібно
if(!$DJ->is_auth()){
    $rez = $DJ->auth();
    if (!$rez) sys::print(code: 401, title: "Unauthorized");
}

//Встановлення параметрів для пошуку вакансій
$DJ->start_search(pages: 2)
    ->add_specialization("PHP")
    ->add_english("no_english")->add_english("basic")->add_english("pre")
    ->add_salaryFrom("2500")
    ->add_employment("remote");

$arr = [
    "profileView" => $DJ->load_profileView(),           //Список користувачів що дивилися ваш профіль за останній місяць
    "jobsFilter" => $DJ->load_jobsFilter(),             //Дерево параметрів для пощуку
    "search" => $DJ->load_jobsBySearch(),               //Список вакансій, котрі знайшлись по заданим параметрам пошуку
    "archive" => $DJ->load_inbox(is_archive: true),     //Список повідомлень із архіва
    "inbox" => $DJ->load_inbox()                        //Список активних повідомленнь
];
sys::print($arr);
```
