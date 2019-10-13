<?php

declare(strict_types=1);

namespace Connehito\CakephpMasterReplica\Test\TestCase\Database\Connection;

use Cake\Database\Connection;
use Cake\Database\Driver;
use Cake\Database\Driver\Mysql;
use Cake\Database\DriverInterface;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Connehito\CakephpMasterReplica\Database\Connection\MasterReplicaConnection;
use ReflectionProperty;

class MasterReplicaConnectionTest extends TestCase
{
    /**
     * @var MasterReplicaConnection
     */
    private $subject;

    /**
     * @var array Connection configuration
     */
    private $config = [
        'driver' => Mysql::class,
        'host' => 'example.com',
        'username' => 'user',
        'password' => 'password',
        'database' => 'dbname',
        'roles' => [
            'master' => ['password' => 'password_for_master'],
            'replica' => null,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->subject = new MasterReplicaConnection($this->config);
    }

    /**
     * @test
     *
     * @return void
     */
    public function constructToSetDrivers()
    {
        $getDriverConfig = function (DriverInterface $driver) {
            $configProperty = new ReflectionProperty($driver, '_config');
            $configProperty->setAccessible(true);
            $config = $configProperty->getValue($driver);

            return $config;
        };

        $defaultDriver = (new Connection($this->config))->getDriver();
        $defaultConfig = $getDriverConfig($defaultDriver);

        $driversProperty = new ReflectionProperty($this->subject, 'drivers');
        $driversProperty->setAccessible(true);
        $drivers = $driversProperty->getValue($this->subject);

        $this->assertCount(
            count($this->config['roles']),
            $drivers,
            'Some roles are ignored'
        );

        $this->assertContainsOnlyInstancesOf(
            $this->config['driver'],
            $drivers,
            'Some roles could not create'
        );

        $masterConfig = $getDriverConfig($drivers['master']);
        $expected = ['password' => $this->config['roles']['master']['password']];
        $actual = Hash::diff($masterConfig, $defaultConfig);
        $this->assertSame(
            $expected,
            $actual,
            'The value specified by role is not set'
        );

        $replicaConfig = $getDriverConfig($drivers['replica']);
        $actual = Hash::diff($replicaConfig, $defaultConfig);
        $this->assertEmpty(
            $actual,
            'Default values are not copied to empty-configured role'
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function constructToSetDefaultRole()
    {
        $driversProperty = new ReflectionProperty($this->subject, 'drivers');
        $driversProperty->setAccessible(true);
        $drivers = $driversProperty->getValue($this->subject);

        $driverProperty = new ReflectionProperty($this->subject, '_driver');
        $driverProperty->setAccessible(true);
        $actual = $driverProperty->getValue($this->subject);

        $this->assertSame(
            $drivers['master'],
            $actual,
            'Default role instance is not set in construction'
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function switchRole()
    {
        $roleProperty = new ReflectionProperty($this->subject, 'role');
        $roleProperty->setAccessible(true);

        $driverConfigProperty = new ReflectionProperty(Driver::class, '_config');
        $driverConfigProperty->setAccessible(true);

        $this->subject->switchRole('replica');

        $actualRole = $roleProperty->getValue($this->subject);
        $this->assertSame(
            'replica',
            $actualRole,
            'The role is not switched'
        );
        $actualConfig = $driverConfigProperty->getValue($this->subject->getDriver());
        $this->assertSame(
            $this->config['password'],
            $actualConfig['password'],
            'The driver is not set as replica role.'
        );

        $this->subject->switchRole('master');
        $actualConfig = $driverConfigProperty->getValue($this->subject->getDriver());
        $this->assertSame(
            $this->config['roles']['master']['password'],
            $actualConfig['password'],
            'The driver is not set as master role.'
        );
    }

    /**
     * @test
     *
     * @depends switchRole
     * @return  void
     */
    public function getDriver()
    {
        $driversProperty = new ReflectionProperty($this->subject, 'drivers');
        $driversProperty->setAccessible(true);
        $drivers = $driversProperty->getValue($this->subject);

        foreach (array_keys($this->config['roles']) as $role) {
            $this->subject->switchRole($role);

            $this->assertSame(
                $drivers[$role],
                $this->subject->getDriver(),
                "getDriver() doesnt return current role instance in {$role}"
            );
        }
    }
}
