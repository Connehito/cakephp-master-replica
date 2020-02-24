<?php
declare(strict_types=1);

namespace Connehito\CakephpMasterReplica\Test\TestCase;

use Cake\Auth\WeakPasswordHasher;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use Connehito\CakephpMasterReplica\Database\Connection\MasterReplicaConnection;
use PDOException;

final class MasterReplicaConnectionIntegrationTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        $connection = ConnectionManager::get('test');
        assert($connection instanceof MasterReplicaConnection);
        $connection->switchRole('master');

        parent::tearDown();
    }

    /**
     * @test
     */
    public function switchRoleThenChangingSession(): void
    {
        $subject = ConnectionManager::get('test');
        assert($subject instanceof MasterReplicaConnection);

        foreach ($subject->config()['roles'] as $role => $config) {
            $subject->switchRole($role);
            $query = $subject->query('SELECT CURRENT_USER();');
            $this->assertTrue($query->execute());
            $this->assertSame(
                "{$config['username']}@%",
                $query->fetch()[0],
                "Not connected as {$role}."
            );
        }
    }

    /**
     * @test
     */
    public function switchToReadOnlyUserThenNotWritable(): void
    {
        $table = $this->getTableLocator()->get('Users');
        $query = $table->query()
            ->insert(['name', 'email', 'password'])
            ->values(
                [
                    'name' => 'Taro Suzuki',
                    'email' => 'mail@example.com',
                    'password' => (new WeakPasswordHasher())->hash('secret'),
                ]
            );

        // Default user(master) is writable.
        $query->execute();

        $connection = $table->getConnection();
        assert($connection instanceof MasterReplicaConnection);
        // Secondary user is not writable, so PDO throws the exception.
        $connection->switchRole('secondary');
        $this->expectException(PDOException::class);
        $this->expectExceptionMessageRegExp(
            "/INSERT command denied to user '(.*?)'@'(.*?)' for table 'users'/"
        );
        $query->cleanCopy()->execute();
    }
}
