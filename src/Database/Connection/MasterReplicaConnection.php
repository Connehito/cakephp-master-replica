<?php
declare(strict_types=1);

namespace Connehito\CakephpMasterReplica\Database\Connection;

use Cake\Database\Connection;
use Cake\Database\DriverInterface;

/**
 * Connection for handling multi connection(driver) to database.
 */
class MasterReplicaConnection extends Connection
{
    /**
     * @var string Current role name
     */
    protected $role = 'master';

    /**
     * @var \Cake\Database\Driver[] Driver instances
     */
    protected $drivers = [];

    /**
     * {@inheritdoc}
     *
     * Create all roles' driver instance and set default role's as current driver.
     */
    public function __construct($config)
    {
        assert(is_array($config['roles']) && count($config['roles']));

        parent::__construct($config);

        $driverClass = $config['driver'];
        foreach ($config['roles'] as $role => $override) {
            if (is_null($override)) {
                $driver = parent::getDriver();
            } else {
                $roleConfig = $override + $config;
                $driver = new $driverClass($roleConfig);
            }
            $this->drivers[$role] = $driver;
        }
        $this->switchRole($this->role);
    }

    /**
     * Switch driver to connect to DB
     *
     * @param string $role master or replica
     * @return $this
     */
    public function switchRole(string $role)
    {
        $this->role = $role;
        $driver = $this->getDriver();
        parent::setDriver($driver);

        return $this;
    }

    /**
     * Get current role's driver
     *
     * @return \Cake\Database\DriverInterface Current role's driver
     */
    public function getDriver(): DriverInterface
    {
        return $this->drivers[$this->role];
    }
}
