## Запуск

> TODO: Завернуть в Makefile

> TODO: wait-for-it.sh

```shell
docker-compose up -d

# Установить зависимости
docker-compose run composer bash -c "composer install && composer dump-autoload --optimize"

# Собрать фронт
docker-compose exec node sh -c "yarn install && yarn build"

# Подождать БД...
# Запустить миграции
docker-compose exec php sh -c "php bin/console d:m:m --no-interaction"
```

> Приложение крутится на хосте `positron.com` (nginx-конфиг: `docker/nginx`),
> поэтому надо подправить `/etc/hosts`.

> **Now, it's available at `localhost:8080`**

### Админка

Располагается по пути `/admin`.

Пользователя можно создать с помощью команды `app:create-admin`:

```shell
docker-compose exec  php sh -c "php bin/console app:create-admin"
```

### Парсинг

Запускается с помощью команды `app:parse-books`:

**В докере отваливаются транзакции!!**

[https://github.com/yiisoft/yii2/issues/18406 и связанные треды](https://github.com/yiisoft/yii2/issues/18406)

Команда работает через утилиту symfony:

```shell
symfony console app:parse-books -v
```

При запуске не из докера у директории с изображениями будут неверные права доступа. При этом Symfony Server (`symfony serve`), крутящийся на локалхосте, будет работать корректно, и вы сможете полюбоваться изображениями; а Nginx из докера будет отдавать 403. 
Можно подправить права доступа для докера. Хотя бы так:

```shel
chmod -R 777 public/uploads
```

И тогда изображения будут доступны и на `positron.com`.

### Каптча

Мои слитые (подаренные) ключи привязаны к `localhost:8000`, поэтому форма обратной связи не будет работать
на `positron.com`: добавьте ваши ключи, например, в `.env.local`.

___

Главное замечание по проделанной работе: мало известно о доменной модели!.

Отсюда вопросы:

* Могут ли повторяться ISBN?
* Как обрабатывать одинаковые имена авторов?
* И т. д. и т. п.
