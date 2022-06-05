<h1 align="center">
  <img src="icon.png" height="150" width="150" alt="FBT"/>
</h1>

# FBT sync

This library allows you to import native phrases and automatically deploy reviewed translations.

[Get started with Swiftyper Translations](https://translations.swiftyper.sk)

## Requirements
* PHP 7.0 or higher

## 📦 Installing

```shell
$ composer require swiftyper/fbt-sync
```

## 🔧 Configuration

These steps are required:
1. Register your FBT project on [Swiftyper Translations](https://translations.swiftyper.sk)

2. Create config file and adjust the values accordingly for file `swiftyper_config.php`:
    ```shell
    $ php ./vendor/bin/swiftyper fbt --config
    ```

3. Init project settings:
    ```shell
    $ php ./vendor/bin/swiftyper fbt --init
    ```

4. *[OPTIONAL]* If you want to use automatic import & deploy:
   - Create this 3 POST endpoints:

       ```php
       $swiftyper = new \Swiftyper\fbt\SwiftyperIntlRouter();
    
       // yourdomain.com/intl/sync:
       $swiftyper->sync();
    
       // yourdomain.com/intl/deploy:
       $swiftyper->deploy();
    
       // yourdomain.com/intl/upload:
       $swiftyper->upload();
       ```

### Options

A `swiftyper_config.php` file will be stored in your project root. Edit the contents of this file and adjust the values accordingly.
You need to provide a valid Swiftyper **api_token** value:

* **api_key** `string`: Project API key (required)
* **verify_signature** `bool`: Verify signature from response
  * **fbt**:
    * **path** `string`: Cache storage path for generated translations & source strings
    * **hash_module** `string`: Hash module
    * **md5_digest** `string`: MD5 digest

## 	🚀 Command

```shell
php ./vendor/bin/swiftyper
```

### Options

| Option          | Description                                         |
|-----------------|-----------------------------------------------------|
| --deploy        | Deploy reviewed app translations                    |
| --upload=[path] | Upload phrases/translations to swiftyper            |
| --init          | Connect fbt project with swiftyper                  |
| --config        | Creates `swiftyper_config.php` in your project root |
| --pretty        | Pretty print output                                 |

## 📜 License
The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.