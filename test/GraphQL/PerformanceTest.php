<?php

namespace ZFTest\Doctrine\GraphQL\GraphQL;

use ZFTest\Doctrine\GraphQL\AbstractTest;
use GraphQL\GraphQL;
use Db\Entity;

class PerformanceTest extends AbstractTest
{
    public function testPerformanceEntity()
    {
        $schema = $this->getSchema();

        $query = "{ performance { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(5, sizeof($output['data']['performance']));
    }

    public function testArtistManyToOne()
    {
        $schema = $this->getSchema();

        $query = "{ performance { id artist { name } } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals('artist1', $output['data']['performance'][0]['artist']['name']);
    }
}
