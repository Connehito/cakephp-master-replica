<?php
/**
 * Test suite bootstrap.
 *
 * This function is used to find the location of CakePHP whether CakePHP
 * has been installed as a dependency of the plugin, or the plugin is itself
 * installed as a dependency of an application.
 */

use Cake\Database\Driver\Mysql;
use Cake\Datasource\ConnectionManager;
use Connehito\CakephpMasterReplica\Database\Connection\MasterReplicaConnection;

$findRoot = function ($root) {
    do {
        $lastRoot = $root;
        $root = dirname($root);
        if (is_dir($root . '/vendor/cakephp/cakephp')) {
            return $root;
        }
    } while ($root !== $lastRoot);
    throw new Exception("Cannot find the root of the application, unable to run tests");
};
$root = $findRoot(__FILE__);
unset($findRoot);
chdir($root);
if (file_exists($root . '/config/bootstrap.php')) {
    require $root . '/config/bootstrap.php';
}

require $root . '/vendor/cakephp/cakephp/tests/bootstrap.php';

ConnectionManager::drop('test');
ConnectionManager::setConfig([
    'test' => [
        'driver' => Mysql::class,
        'className' => MasterReplicaConnection::class,
        'host' => env('DB_HOST'),
        'database' => 'test',
        'roles' => [
            'master' => ['username' => 'my_app', 'password' => 'secret'],
            'secondary' => ['username' => 'second_user', 'password' => 'secretsecret'],
            'tertiary' => ['username' => 'third_user', 'password' => 'secretsecretsecret'],
        ]
    ]
]);
