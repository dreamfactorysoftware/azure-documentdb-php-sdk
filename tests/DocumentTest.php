<?php
require dirname(__FILE__) . '/../vendor/autoload.php';
require dirname(__FILE__) . '/../dev-test-files/configs.php';

class DocumentTest extends PHPUnit_Framework_TestCase
{
    /** @var  \DreamFactory\DocumentDb\Contracts\ClientInterface */
    protected $client;

    /** @var  \DreamFactory\DocumentDb\Resources\Database */
    protected $db;

    /** @var  \DreamFactory\DocumentDb\Resources\Database */
    protected $coll;

    /** @var  \DreamFactory\DocumentDb\Resources\Document */
    protected $doc;

    public function setUp()
    {
        parent::setUp();
        $this->client = new \DreamFactory\DocumentDb\Client(AZURE_URI, AZURE_KEY);
        $this->db = new \DreamFactory\DocumentDb\Resources\Database($this->client);
        $this->db->create(['id' => 'unit-test-db-coll']);
        $this->coll = new \DreamFactory\DocumentDb\Resources\Collection($this->client, 'unit-test-db-coll');
        $this->coll->create(['id' => 'collection']);
        $this->doc = new \DreamFactory\DocumentDb\Resources\Document($this->client, 'unit-test-db-coll', 'collection');
    }

    public function testCreateDocument()
    {
        $rs = $this->doc->create(['id'=>'doc1', 'name' => 'doc1']);
        $this->assertEquals('doc1', $rs['id']);
        $this->doc->delete('doc1');
    }

    public function testGetDocument()
    {
        $this->doc->create(['id'=>'doc2', 'name' => 'doc2']);
        $rs = $this->doc->get('doc2');
        $this->assertEquals('doc2', $rs['id']);
        $this->doc->delete('doc2');
    }

    public function testListDocument()
    {
        $this->doc->create(['id'=>'doc3', 'name' => 'doc3']);
        $rs = $this->doc->getAll();
        $docs = $rs['Documents'];
        $list = [];
        foreach ($docs as $doc){
            $list[] = $doc['id'];
        }
        $this->assertTrue(in_array('doc3', $list));
        $this->doc->delete('doc3');
    }

    public function testReplaceDocument()
    {
        $this->doc->create(['id'=>'doc4', 'name' => 'doc4']);
        $this->doc->replace(['id' => 'doc4', 'name' => 'doc4-replaced'], 'doc4');
        $rs = $this->doc->get('doc4');
        $this->assertEquals('doc4-replaced', $rs['name']);
        $this->doc->delete('doc4');
    }

    public function testQueryDocument()
    {
        $this->doc->create(['id'=>'doc5', 'name' => 'doc5']);
        $sql = "SELECT * FROM collection WHERE collection.name = @name";
        $params = [['name' => '@name', 'value' => 'doc5']];
        $rs = $this->doc->query($sql, $params);
        $docs = $rs['Documents'];
        $list = [];
        foreach ($docs as $doc){
            $list[] = $doc['id'];
        }
        $this->assertTrue(in_array('doc5', $list));
        $this->doc->delete('doc5');
    }

    public function testDeleteDocument()
    {
        $this->doc->create(['id' => 'doc6a']);
        $rs = $this->doc->create(['id' => 'doc6']);
        $this->assertEquals('doc6', $rs['id']);
        $this->doc->delete('doc6');
        $rs = $this->doc->getAll();
        $docs = $rs['Documents'];
        $list = [];
        foreach ($docs as $doc){
            $list[] = $doc['id'];
        }
        $this->assertFalse(in_array('doc6', $list));
        $this->doc->delete('doc6a');
        $this->db->delete('unit-test-db-coll');
    }
}