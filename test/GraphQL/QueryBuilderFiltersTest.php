<?php

namespace ZFTest\Doctrine\GraphQL\GraphQL;

use ZFTest\Doctrine\GraphQL\AbstractTest;
use GraphQL\GraphQL;
use Db\Entity;

class QueryBuilderFiltersTest extends AbstractTest
{
    public function testEquals()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { id_eq: { value: 1 } } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(1, sizeof($output['data']['performance']));
    }

    public function testNotEquals()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { id_neq: { value: 1 } } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(4, sizeof($output['data']['performance']));
    }

    public function testGreaterThan()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { id_gt: { value: 1 } } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(4, sizeof($output['data']['performance']));
    }

    public function testGreaterThanOrEquals()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { id_gte: { value: 2 } } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(4, sizeof($output['data']['performance']));
    }

    public function testLessThan()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { id_lt: { value: 2 } } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(1, sizeof($output['data']['performance']));
    }

    public function testLessThanOrEquals()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { id_lte: { value: 2 } } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(2, sizeof($output['data']['performance']));
    }

    public function testIsNull()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { isTradable_isnull: {} } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(1, sizeof($output['data']['performance']));
    }

    public function testIsNotNull()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { isTradable_isnotnull: {} } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(4, sizeof($output['data']['performance']));
    }

    public function testIn()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { id_in: { values: [3, 4] } } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(2, sizeof($output['data']['performance']));
    }

    public function testNotIn()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { id_notin: { values: [3, 4] } } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(3, sizeof($output['data']['performance']));
    }

    public function testBetween()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { id_between: { from: 2 to: 4} } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(3, sizeof($output['data']['performance']));
    }

    public function testContains()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { venue_contains: { value: \"enue\" } } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(5, sizeof($output['data']['performance']));
    }

    public function testStartsWith()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { venue_startswith: { value: \"v\" } } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(5, sizeof($output['data']['performance']));
    }

    public function testEndsWith()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { venue_endswith: { value: \"5\" } } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(1, sizeof($output['data']['performance']));
    }

    public function testSortAsc()
    {
        $schema = $this->getSchema();

        $query = '{ performance ( filter: { venue_sort:"asc" } ) { id venue performanceDate } }';

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals('venue1', $output['data']['performance'][0]['venue']);
    }

    public function testSortDesc()
    {
        $schema = $this->getSchema();

        $query = '{ performance ( filter: { venue_sort:"desc" } ) { id venue performanceDate } }';

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals('venue5', $output['data']['performance'][0]['venue']);
    }

    public function testSkip()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { _skip: 3 } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(2, sizeof($output['data']['performance']));
    }

    public function testLimit()
    {
        $schema = $this->getSchema();

        $query = "{ performance ( filter: { _limit: 3 } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertEquals(3, sizeof($output['data']['performance']));
    }
}
