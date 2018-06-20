<?php

namespace ZFTest\Doctrine\GraphQL\GraphQL;

use ZFTest\Doctrine\GraphQL\AbstractTest;
use GraphQL\GraphQL;
use Db\Entity;

class AddressTest extends AbstractTest
{
    /**
     * @dataProvider schemaDataProvider
     */
    public function testAddressEntity($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ address { id address } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(5, sizeof($output['data']['address']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testAddressFilterId($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ address (filter: { id:1 }) { id address } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(1, sizeof($output['data']['address']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testAddressUser1to1($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ address (filter: { id:1 }) { id address user { id name } } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals('test1', $output['data']['address'][0]['user']['name']);
    }
}
