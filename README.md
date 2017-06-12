# azure-documentdb-php-sdk
PHP SDK for Azure DocumentDB

## Usage

    $client = new DreamFactory\DocumentDb\Client('azure_host_uri', 'azure_document_db_key');
    
    $db = new \DreamFactory\DocumentDb\Resources\Database($client);
    $db->getAll()
    $db->get('db-id');
    $db->create(['id'=>'my_db']);
    $db->delete('db-id');
    
    //To set additional optional headers
    $db->setHeaders(['Content-Type: application/json']);
    
    $coll = new \DreamFactory\DocumentDb\Resources\Collection($client, 'db-id');
    $coll->getAll();
    $coll->get('coll-id');
    $coll->create(['id'=>'1']);
    $coll->replace(['id'=>'1', 'indexingPolicy'=>[...]], 'coll-id');
    $coll->delete('coll-id');
    
    //To set additional optional headers
    $coll->setHeaders(['Content-Type: application/json']);
    
    $doc = new \DreamFactory\DocumentDb\Resources\Document($client, 'db-id', 'coll-id');
    $doc->getAll();
    $doc->get('doc-id');
    $doc->create(['id'=>'1', 'name'=>'foobar']);
    $doc->replace(['id'=>'1', 'name'=>'foobar-replaced'], 'doc-1');
    $doc->query('SELECT * FROM coll WHERE coll.name = @name', [['name' => '@name', 'value' => 'foobar']]);
    $doc->delete('doc-id');
    
    //To set additional optional headers
    $doc->setHeaders(['Content-Type: application/json']);
    
## Note

Only Database, Collection, and Document operations are supported now.
