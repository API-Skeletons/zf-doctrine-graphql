<?php

namespace ZFTest\Doctrine\GraphQL\GraphQL;

use ZFTest\Doctrine\GraphQL\AbstractTest;
use GraphQL\GraphQL;
use Db\Entity;

class AddressTest extends AbstractTest
{
    public function testAddressEntity()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ address { id address } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(5, sizeof($output['data']['address']));
        }
    }

    public function testAddressFilterId()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ address (filter: { id:1 }) { id address } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(1, sizeof($output['data']['address']));
        }
    }

    public function testAddressUser1to1()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ address (filter: { id:1 }) { id address user { id name } } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals('test1', $output['data']['address'][0]['user']['name']);
        }
    }
}
