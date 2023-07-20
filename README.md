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
    "jobsFilter" => $DJ->load_jobsFilter(),   //Обьект указателей на поиск
    "archive" => $DJ->load_inbox(is_archive: true),  //получение сообщений из архива
    "inbox" => $DJ->load_inbox()   //ПОлучение сообщений из почтового ящика
];

sys::print($arr);
```

.

.

#### Message object example
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
    "name": "Beliani",
    "img": "https://p.djinni.co/05/ee7b05b7a7966223a16a942b37cef1/1677054671213_400.jpg",
    "url": "https://djinni.co/jobs/?company=beliani-com-837ef",
    "is_top": false
  }
}
```

.

.

#### Example of a search options object
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