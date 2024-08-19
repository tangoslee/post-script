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
php artisan vendor:publish --provider="Tangoslee\PostScript\PostScriptServiceProvider"
```
- Run the migrations: After the config and migration have been published and configured, you can create the tables for this package by running:
```bash
php artisan migrate
```

## Usages

### Create new post-script
```bash
php artissan make:post-script foo_bar
```
- output example
```bash
/home/user/project/post-scripts/2024/08/2024_08_19_125037_foo_bar.sh created
```

### Edit a script
- Edit a shell script 

### Show status
```bash
php artisan post-script:status
```
- output example
```text
+------+------------------------------+------------+----+
| Ran? | Script                       | Batch      | ID |
+------+------------------------------+------------+----+
| Yes  | 2024_08_09_123456_script.sh  | 1723977236 | 1  |
| No   | 2024_08_19_125037_foo_bar.sh |            |    |
+------+------------------------------+------------+----+
```

### Run all scripts that have not been executed.
```bash
php artisan post-script:run
```

### Run script again
- example. If you want to run ID 1 script again for some reason.
```text
+------+------------------------------+------------+----+
| Ran? | Script                       | Batch      | ID |
+------+------------------------------+------------+----+
| Yes  | 2024_08_09_123456_script.sh  | 1723977236 | 1  |
| No   | 2024_08_19_125037_foo_bar.sh |            |    |
+------+------------------------------+------------+----+
```
```bash
php artisan post-script:run --replay 1
```
