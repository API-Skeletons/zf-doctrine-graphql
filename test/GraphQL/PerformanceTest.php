<?php

namespace ZFTest\Doctrine\GraphQL\GraphQL;

use ZFTest\Doctrine\GraphQL\AbstractTest;
use GraphQL\GraphQL;
use Db\Entity;

class PerformanceTest extends AbstractTest
{
    /**
     * @dataProvider schemaDataProvider
     */
    public function testPerformanceEntity($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(5, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testArtistManyToOne($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance { id artist { name } } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals('artist1', $output['data']['performance'][0]['artist']['name']);
    }
}
