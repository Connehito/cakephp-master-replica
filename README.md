# [Archived] CakePHP Master Replica Plugin

Use CakePHP native features introduced from [4.5](https://book.cakephp.org/4/en/appendices/4-5-migration-guide.html#database) if you want to switch Database roles.

This project has been archived and is no longer actively maintained because several years have passed without active maintenance.

We appreciate the support and contributions from the community over the years. While this repository will remain available in read-only mode, there will be no further updates or maintenance. Thank you for your understanding and support.

-----

The datasource for CakePHP.This plugin enables one-connection to act as two(or more) roles, like master(read-write) and replica(read-only).

[![No Maintenance Intended](http://unmaintained.tech/badge.svg)](http://unmaintained.tech/)
[![Build Status](https://travis-ci.com/Connehito/cakephp-master-replica.svg?branch=master)](https://travis-ci.com/Connehito/cakephp-master-replica)
[![codecov](https://codecov.io/gh/Connehito/cakephp-master-replica/branch/master/graph/badge.svg)](https://codecov.io/gh/Connehito/cakephp-master-replica)
[![Latest Stable Version](https://poser.pugx.org/connehito/cakephp-master-replica/v/stable)](https://packagist.org/packages/Connehito/cakephp-master-replica)
[![Total Downloads](https://poser.pugx.org/connehito/cakephp-master-replica/downloads)](https://packagist.org/packages/Connehito/cakephp-master-replica)
[![License](https://poser.pugx.org/connehito/cakephp-master-replica/license)](https://packagist.org/packages/Connehito/cakephp-master-replica)

## Supports

- PHP 7.2+
- CakePHP 4.0+

:bulb: For CakePHP3.x, use `1.x` branch.

## Usage

1. Install plugin `composer require connehito/cakephp-master-replica`
2. Set your connections(e.g. in `config/app.php` datasource. It requires `roles` property.

### Example

Set up your database configuration.

- Databse-A(for master): mysql;host=db-host,databasename=app_db,login=root,pass=password
- Databse-B(for replica): mysql;host=db-host,databasename=app_db,login=read-only-user,pass=another-password
- Databse-C(for replica): mysql;host=replica-host,databasename=app_db,login=read-only-user,pass=another-password

```php
// app.php
// return [
    'Datasources' => [
        'driver' => Cake\Database\Driver\Mysql::class,
        'className' => Connehito\CakephpMasterReplica\Database\Connection\MasterReplicaConnection::class,
        'host' => 'replica-host',
        'database' => 'app_db',
        'roles' => [
            'master' => ['host' => 'db-host', 'username' => 'root', 'password' => 'password'],
            'secondary' => ['host' => 'db-host', 'username' => 'read-only-user', 'password' => 'another-password'],
            'tertiary' => ['username' => 'read-only-user', 'password' => 'another-password'],
        ]
    ]
```

In each roles, you can set specific values and override them.

In app, now you can connect to database master or replica db as you like :tada:

```php
// as default, connect with `master` role.
$usersTable->save($usersTable->newEntity(['name' => 'hoge']));

// switch to `replica` role
\Cake\Datasource\ConnectionManager::get('default')->switchRole('secondary');
// Or you can get Connection via Table
$usersTable->getConnection()->switchRole('tertiary');
```

## Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/Connehito/cakephp-master-replica 

- Please fix all problems with PHPUnit, PHPStan, PHP_CodeSniffer before send pull request :smile:
- You can build development env in local with `tests/test_app/docker-compose.yml` :horse_racing:

## License

The plugin is available as open source under the terms of the [MIT License](http://opensource.org/licenses/MIT).
