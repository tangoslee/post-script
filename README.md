## Installing

- This package publishes a config/post_script.php file. If you already have a file by that name, please rename or remove it.
- You can install the package via composer:
```bash
 composer require tangoslee/post-script
```
- Optional: The service provider will automatically get registered. Or you may manually add the service provider in your config/app.php file:
```bash
'providers' => [
    // ...
    Tangoslee\PostScript\PostScriptServiceProvider::class,
];
```
- You should publish the migration and the config/post_script.php config file with:
```bash
php artisan vendor:publish --provider="Tangos\PostScript\PostScriptServiceProvider"
```
- Run the migrations: After the config and migration have been published and configured, you can create the tables for this package by running:
```bash
php artisan migrate
```

## Usages


