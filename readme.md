## GraphGrailAi platform. Web client


### First deploy

- Нужна база данных и редис.
- Установить php-расширение pcntl. Нужно для работы с очередями
- Скопировать `.env.example` в `.env` и заполнить
- `composer install`
- `npm install`
- `php artisan key:generate `
- `php artisan migrate`
- На супервизор повесить `php artisan horizon`

Пример конфигурации с офф сайта 
> [program:horizon]
>
> process_name=%(program_name)s
>
> command=php /home/forge/app.com/artisan horizon
>
> autostart=true
>
> autorestart=true
>
> user=forge
>
> redirect_stderr=true
>
> stdout_logfile=/home/forge/app.com/horizon.log
