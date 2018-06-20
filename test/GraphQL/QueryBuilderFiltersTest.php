<?php

namespace ZFTest\Doctrine\GraphQL\GraphQL;

use ZFTest\Doctrine\GraphQL\AbstractTest;
use GraphQL\GraphQL;
use Db\Entity;

class QueryBuilderFiltersTest extends AbstractTest
{
    public function testEquals()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { id_eq: 1 } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(1, sizeof($output['data']['performance']));
        }
    }

    public function testNotEquals()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { id_neq: 1 } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(4, sizeof($output['data']['performance']));
        }
    }

    public function testGreaterThan()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { id_gt: 1 } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(4, sizeof($output['data']['performance']));
        }
    }

    public function testGreaterThanOrEquals()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { id_gte: 2 } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(4, sizeof($output['data']['performance']));
        }
    }

    public function testLessThan()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { id_lt: 2 } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(1, sizeof($output['data']['performance']));
        }
    }

    public function testLessThanOrEquals()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { id_lte: 2 } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(2, sizeof($output['data']['performance']));
        }
    }

    public function testIsNull()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { isTradable_isnull:true } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(1, sizeof($output['data']['performance']));
        }
    }

    public function testIsNotNull()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { isTradable_isnull:false } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(4, sizeof($output['data']['performance']));
        }
    }

    public function testIn()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { id_in: [3, 4] } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(2, sizeof($output['data']['performance']));
        }
    }

    public function testNotIn()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { id_notin: [3, 4] } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(3, sizeof($output['data']['performance']));
        }
    }

    public function testBetween()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { id_between: { from: 2 to: 4} } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(3, sizeof($output['data']['performance']));
        }
    }

    public function testContains()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { venue_contains: \"enue\" } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(5, sizeof($output['data']['performance']));
        }
    }

    public function testStartsWith()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { venue_startswith: \"v\" } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(5, sizeof($output['data']['performance']));
        }
    }

    public function testEndsWith()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { venue_endswith: \"5\" } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(1, sizeof($output['data']['performance']));
        }
    }

    public function testSortAsc()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = '{ performance ( filter: { venue_sort:"asc" } ) { id venue performanceDate } }';

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals('venue1', $output['data']['performance'][0]['venue']);
        }
    }

    public function testSortDesc()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = '{ performance ( filter: { venue_sort:"desc" } ) { id venue performanceDate } }';

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals('venue5', $output['data']['performance'][0]['venue']);
        }
    }

    public function testSkip()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { _skip: 3 } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(2, sizeof($output['data']['performance']));
        }
    }

    public function testLimit()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { _limit: 3 } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(3, sizeof($output['data']['performance']));
        }
    }

    public function testOverTheLimit()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ performance ( filter: { _limit: 10000 } ) { id performanceDate } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(5, sizeof($output['data']['performance']));
        }
    }
}
