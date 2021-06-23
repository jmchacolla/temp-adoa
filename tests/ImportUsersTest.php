<?php
namespace Tests\Adoa;

require_once(__DIR__ . '/../classes/MigrateUsersProd.php');

use Tests\Feature\Shared\RequestHelper;
use Tests\TestCase;
use ProcessMaker\Adoa\classes\MigrateUsersProd;
use Mockery;

class ImportUsersTest extends TestCase
{
    use RequestHelper;

    public function testImportUsers()
    {
        $clientMock = Mockery::mock('Client');
        $clientMock
            ->shouldReceive('request')
            ->with(Mockery::any(), Mockery::any(), Mockery::on(function($config) {
                copy(__DIR__ . '/users-example.csv', $config['sink']);
                return true;
            }));
        $migrateUsers = Mockery::mock(MigrateUsersProd::class . '[client]');
        $migrateUsers->shouldReceive('client')->andReturn($clientMock);

        $data = [];
        $migrateUsers->getAdoaExternalUsers(function($row) use (&$data) {
            $data[] = $row;
        });
        
        $this->assertCount(8, $data);
        $this->assertEquals($data[3][11], 'DEPT OF ECONOMIC SECURITY');
    }
}