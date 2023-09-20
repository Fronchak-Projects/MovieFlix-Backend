<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# MovieFlix Laravel

## Requirements
* PHP 8.0
* Composer
* A database of your preference (MySQL recommended)

## How to Use
1. Clone this project to your machine
2. Open your terminal in the project's folder
3. Run the following command: 'composer update' to install all the dependencies
4. In the 'storage/app/public' folder create a new folder named 'imgs'
5. Inside the new folder created in the last step, create threee more folders, with the following names: 'genres', 'movies', and 'users'
6. Run the following command: 'php artisan storage:link'
7. Create and config you '.env' file using the '.env.example' as an example.
8. Run the following command: 'php artisan migrate' to run all the migrations
9. In the UserSeeder.php file,  localized in the 'database/seeders' folder you can change the password of the default users, if you want, before run the all the seeds
10. Run the following command: 'php artisan db:seed' to run the seeds
11. Run the following command: 'php artisan serve' to initialize the backend
12. Setup the frontend project following the instructions in the [project page](https://github.com/Fronchak-Projects/MovieFlix-Frontend)
