# pulsedb
Pulse Data Base Connector

## Installation & loading
PulseDB is available on [Packagist](https://packagist.org/packages/pulseframework/pulsedb) (using semantic versioning), and installation via [Composer](https://getcomposer.org) is the recommended way to install PulseDB. Just add this line to your `composer.json` file:

```json
"pulseframework/pulsedb": "~1.0"
```

or run

```sh
composer require pulseframework/pulsedb
```

Note that the `vendor` folder and the `vendor/autoload.php` script are generated by Composer; they are not part of PulseDB


You need to define: `_DB_PREFIX_`, `_DB_NAME_`, `_DB_SERVER_`, `_DB_USER_`, `_DB_PASSWD_`, `_PULSE_DEBUG_SQL_`, `_PULSE_MODE_DEV_`, `_PULSE_CACHE_ENABLED_`, `_PULSE_MAGIC_QUOTES_GPC_`, `_PULSE_CACHING_SYSTEM_`, `_PULSE_USE_SQL_SLAVE_`
