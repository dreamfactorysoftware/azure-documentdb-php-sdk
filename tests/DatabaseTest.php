<?php
require dirname(__FILE__) . '/../vendor/autoload.php';
require dirname(__FILE__) . '/../dev-test-files/configs.php';

class DatabaseTest extends PHPUnit_Framework_TestCase
{
    /** @var  \DreamFactory\DocumentDb\Contracts\ClientInterface */
    protected $client;

    /** @var  \DreamFactory\DocumentDb\Resources\Database */
    protected $db;

    public function setUp()
    {
        parent::setUp();
        $this->client = new \DreamFactory\DocumentDb\Client(AZURE_URI, AZURE_KEY);
        $this->db = new \DreamFactory\DocumentDb\Resources\Database($this->client);
    }

    public function tearDown()
    {
        $this->client = null;
        $this->db = null;
        parent::tearDown();
    }

    public function testCreateDatabase()
    {
        $rs = $this->db->create(['id' => 'unit-test-db']);
        $this->assertEquals('unit-test-db', $rs['id']);
        $this->db->delete('unit-test-db');
    }

    public function testListDatabase()
    {
        $this->db->create(['id' => 'unit-test-db1']);
        $this->db->create(['id' => 'unit-test-db2']);
        $rs = $this->db->getAll();
        $dbs = $rs['Databases'];

        $list = [];
        foreach ($dbs as $db){
            $list[] = $db['id'];
        }

        $this->assertTrue(in_array('unit-test-db1', $list));
        $this->assertTrue(in_array('unit-test-db2', $list));

        $this->db->delete('unit-test-db1');
        $this->db->delete('unit-test-db2');
    }

    public function testGetDatabase()
    {
        $this->db->create(['id' => 'unit-test-get']);
        $rs = $this->db->get('unit-test-get');

        $this->assertEquals('unit-test-get', $rs['id']);
        $this->db->delete('unit-test-get');
    }

    public function testDeleteDatabase()
    {
        $rs = $this->db->create(['id' => 'unit-test-delete']);
        $this->assertEquals('unit-test-delete', $rs['id']);
        $this->db->delete('unit-test-delete');
        $rs = $this->db->getAll();
        $dbs = $rs['Databases'];

        $list = [];
        foreach ($dbs as $db){
            $list[] = $db['id'];
        }
        $this->assertFalse(in_array('unit-test-delete', $list));
    }
}