# sycret-task
Test task by Sycret.ru
____

Реализация [задания](./examples/php.pdf)

Пример исходного [шаблона](./examples/forma_025u.xml)

задание выгружено на хостинг по [пути](http://www.u91496b4.beget.tech/)

Базовый запрос на адрес: http://www.u91496b4.beget.tech/gendoc

## Файловая структура проекта:

- examples >> файлы задания
- public_html >> корневая директория сервера
    - .htaccess >> настройки сервера
    - [index.php](./public_html/index.php) >> точка входа в приложение
- src >> директория классов проекта
    - Controllers >> директория классов контроллеров
    - Core >> директория классов ядра проекта (реализация задания)
    - Route >> директория классов роутинга
    - [const.php](./src/const.php) >>
- vendor
- `.env` >> переменные окружения (необходимо создать)
- [.env.example](./.env.example) >> пример файла переменных окружения
- composer.json
- composer.lock

## Доп описание:
В проекте используются следующие пакеты php
```json
"require": {
        "convertapi/convertapi-php": "^1.5",
        "vlucas/phpdotenv": "^5.4"
}
```

Конвертация из doc в pdf производится с помощью API сервиса [convertapi](https://www.convertapi.com/)
