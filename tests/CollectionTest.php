<?php
require dirname(__FILE__) . '/../vendor/autoload.php';
require dirname(__FILE__) . '/../dev-test-files/configs.php';

class CollectionTest extends PHPUnit_Framework_TestCase
{
    /** @var  \DreamFactory\DocumentDb\Contracts\ClientInterface */
    protected $client;

    /** @var  \DreamFactory\DocumentDb\Resources\Database */
    protected $db;

    /** @var  \DreamFactory\DocumentDb\Resources\Database */
    protected $coll;

    public function setUp()
    {
        parent::setUp();
        $this->client = new \DreamFactory\DocumentDb\Client(AZURE_URI, AZURE_KEY);
        $this->db = new \DreamFactory\DocumentDb\Resources\Database($this->client);
        $this->db->create(['id' => 'unit-test-db-coll']);
        $this->coll = new \DreamFactory\DocumentDb\Resources\Collection($this->client, 'unit-test-db-coll');
    }

    public function testCreateCollection()
    {
        $rs = $this->coll->create(['id' => 'unit-test-coll1']);
        $this->assertEquals('unit-test-coll1', $rs['id']);
        $this->coll->delete('unit-test-coll1');
    }

    public function testGetCollection()
    {
        $this->coll->create(['id' => 'unit-test-coll2']);
        $rs = $this->coll->get('unit-test-coll2');
        $this->assertEquals('unit-test-coll2', $rs['id']);
        $this->coll->delete('unit-test-coll2');
    }

    public function testListCollection()
    {
        $this->coll->create(['id' => 'unit-test-coll3']);
        $rs = $this->coll->getAll();
        $colls = $rs['DocumentCollections'];
        $list = [];
        foreach ($colls as $coll){
            $list[] = $coll['id'];
        }
        $this->assertTrue(in_array('unit-test-coll3', $list));
        $this->coll->delete('unit-test-coll3');
    }

    public function testReplaceCollection()
    {
        $this->coll->create(['id' => 'unit-test-coll4']);
        $rs = $this->coll->get('unit-test-coll4');
        $data = [
            'id' => 'unit-test-coll4',
            'indexingPolicy' => $rs['indexingPolicy']
        ];
        $rs = $this->coll->replace($data, 'unit-test-coll4');
        $this->assertEquals('unit-test-coll4', $rs['id']);
        $this->coll->delete('unit-test-coll4');
    }

    public function testDeleteCollection()
    {
        $this->coll->create(['id' => 'unit-test-coll5a']);
        $rs = $this->coll->create(['id' => 'unit-test-coll5']);
        $this->assertEquals('unit-test-coll5', $rs['id']);
        $this->coll->delete('unit-test-coll5');
        $rs = $this->coll->getAll();
        $colls = $rs['DocumentCollections'];
        $list = [];
        foreach ($colls as $coll){
            $list[] = $coll['id'];
        }
        $this->assertFalse(in_array('unit-test-coll5', $list));
        $this->coll->delete('unit-test-coll5a');
        $this->db->delete('unit-test-db-coll');
    }
}