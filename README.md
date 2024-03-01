# Processmaker Package Zj Adoa
This package provides the necessary base code to start the developing a package in ProcessMaker 4.

## Development
If you need to create a new ProcessMaker package run the following commands:

```
git clone https://github.com/ProcessMaker/package-zj-adoa.git
cd package-zj-adoa
php rename-project.php package-zj-adoa
composer install
npm install
npm run dev
```

## Installation
* Use `composer require processmaker/package-zj-adoa` to install the package.
* Use `php artisan package-zj-adoa:install` to install generate the dependencies.
* Use `php artisan vendor:publish` to install generate the dependencies.

## Navigation and testing
* Navigate to administration tab in your ProcessMaker 4
* Select `Skeleton Package` from the administrative sidebar

## Uninstall
* Use `php artisan package-zj-adoa:uninstall` to uninstall the package
* Use `composer remove processmaker/package-zj-adoa` to remove the package completely
