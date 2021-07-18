# Adrian Exam for Kumu

## How to run the project

Requirements
- Docker desktop
- PHP ^8.0 (if not available just run composer install with --ignore-platform-reqs option)

Tech stack
- PHP 8.0.8
- Node 16.4.1
- Laravel Framework
- Tailwind UI Framework
- Docker (containerization)

Install vendor packages, go to the newly cloned project's root directory
```
composer install
```

Copy the environment file template to .env
```
cp .env.example .env
```

Sample configuration of .env
```
# set the application name
APP_NAME=kumu
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://koomoo.test

LOG_CHANNEL=stack
LOG_LEVEL=debug

# set the database name, username and password
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=koomoo
DB_USERNAME=xxx
DB_PASSWORD=xxx

BROADCAST_DRIVER=log

# set this cache driver to redis
CACHE_DRIVER=redis
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=memcached

# setup the redis configuration
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

Start the container, just make sure the ports are available
- application 0.0.0.0:80->80/tcp "laravel application"
- rediscommander 0.0.0.0:8081->8081/tcp "redis gui management tool"
- redis:alpine 0.0.0.0:6379->6379/tcp "cache"
- mysql:8.0 0.0.0.0:3306->3306/tcp "database"

and that there are no existing containers/images with the same name, the initial build
will take longer but succeeding runs will be faster (build --no-cache is used only when
recreating a fresh copy of the container)

```
./vendor/bin/sail build --no-cache
./vendor/bin/sail up
```

If successful, a similar message can be seen:

```
Creating network "koomoo-github-users_sail" with driver "bridge"
Creating koomoo-github-users_redis_1 ... done
Creating koomoo-github-users_mysql_1 ... done
Creating redis-commander                    ... done
Creating koomoo-github-users_laravel.test_1 ... done
```

Do run the asset package manager to generate assets if you want to see the layout of the page
```
npm install
npm run prod
```

Now open your favorite browser and visit http://localhost, you should see a message to create an application key
or alternatively you can run the command

```
./vendor/bin/sail artisan key:generate
```

## How to run the database migration and see the logs
Login to the container

```
./vendor/bin/sail bash
```

Inside the container run the following command to create the database
```
php artisan migrate
```

Inside the container run the following command to monitor the logs
```
cd /var/www/html/storage/logs && tail -f *.log
```

## API Endpoints
Using postman you can send request to the following endpoints, make sure you are using correct
headers, eg: Accept - application/json
- POST - http://localhost/api/register (user registration)
```
{
    "name":"your-name",
    "email":"your-email@test.com",
    "password":"your-password"
}
```

- POST - http://localhost/api/login (user login)
```
{    
    "email":"your-email@test.com",
    "password":"your-password"
}
```

- GET - http://localhost/api/github/users (github user information), requires authentication
Don't forget to send the `Bearer` token in the auth header, then send a json body with
an array of usernames
```
{
    "usernames":["defunkt", "macos", "taylor"]
}
```

## Tools
Redis Commander Gui Tool
- http://localhost/:8081


