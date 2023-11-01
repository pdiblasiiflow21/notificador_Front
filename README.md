<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## Acerca de Boilerplate

Proyecto que sirve de base para aplicaciones futuras. Estas son algunas características:

-   [Formateo de Código](https://github.com/FriendsOfPHP/PHP-CS-Fixer).
-   [Analizador de Código](https://github.com/nunomaduro/larastan).
-   [Visualización de logs](https://github.com/opcodesio/log-viewer).
-   [Telescope](https://github.com/laravel/telescope).
-   [Documentación con swagger](https://github.com/DarkaOnLine/L5-Swagger).
-   [Modularización de código](https://docs.laravelmodules.com/v10/introduction).
-   [Laravel Breeze, sistema de autenticacion y authorizacion](https://laravel.com/docs/10.x/starter-kits#laravel-breeze).
-   [Husky, hooks para commits](https://github.com/typicode/husky)
-   [Laravel-permission, hooks para commits](https://spatie.be/docs/laravel-permission/v5/introduction)

## Learning Laravel

Laravel tiene una gran [documentation](https://laravel.com/docs) para iniciar con el framework.

Se recomienda hacer el [Bootcamp de Laravel](https://bootcamp.laravel.com).

## Uso

Una vez clonado el repositorio, para instalar los paquetes:

```sh
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

Luego
cp .env.example .env

```
### Levanto el contenedor con todos los servicios
```

sail up -d

```
### Creo una clave para la aplicacion
```

sail php artisan key:generate

````

## Para desarrollo solamente
### Si quiero notificar de newsan (ojo que le pega a produccion)
```sh
sail php artisan run:notify-orders-new-san-job
````

### Si quiero borrar los usuarios, roles y permisos existentes

```sh
sail php artisan run:clean-tables
```

### Si quiero cargar los usuarios con permisos de prueba

```sh
sail php artisan db:seed --class=RolesAndPermissionsDemoSeeder
```

### Si quiero hacer un rollback de la ultima migracion

```sh
sail php artisan migrate:rollback
```

### Brindar permisos al directorio storage dentro del laravel.test-1

```sh
chmod 777 -R storage
chmod 777 -R config
```

### Regenerar documentacion

```sh
php artisan l5-swagger:generate
```

### Si quiero cargar los usuarios con permisos de prueba

```sh
sail php artisan db:seed --class=RolesAndPermissionsDemoSeeder
```

### Si quiero ver los permisos del rol

```sh
sail php artisan permission:show
```

### Opcional: para trabajar con los hooks para los commits

Instalar las dependencias de node

```
$ sail npm install -E
```

Correr la instalacion de husky

```
sail npm run prepare
```

Dar permisos al script de husky

```
chmod +x .husky/pre-commit
chmod +x .husky/pre-push
```
