<?php
require dirname(__FILE__) . '/../vendor/autoload.php';

use DreamFactory\DocumentDb\Verbs;
use DreamFactory\DocumentDb\Client;

$client = new Client(
    'https://arif.documents.azure.com:443/',
    'tPfeY3meJSbFGiUQxVgxQwFU7a0sfADYC3LpQYIdOutCOtP1nLRKj2axjlcaIV32poF7rQE5QJsleAWIZpZqwA=='
);

$client::$debug = true;

$data = [
    "id" => '3',
    "name" => "go home 2 replaced",
    "description" => "just go home 233"
];

$rs = $client->request(Verbs::GET, '/dbs/df2/colls/todo/docs', 'docs', 'dbs/df2/colls/todo');

print_r($rs);