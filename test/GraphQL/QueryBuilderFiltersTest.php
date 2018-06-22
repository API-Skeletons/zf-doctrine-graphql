<?php

namespace ZFTest\Doctrine\GraphQL\GraphQL;

use ZFTest\Doctrine\GraphQL\AbstractTest;
use GraphQL\GraphQL;
use Db\Entity;

class QueryBuilderFiltersTest extends AbstractTest
{
    /**
     * @dataProvider schemaDataProvider
     */
    public function testEquals($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { id_eq: 1 } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(1, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testNotEquals($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { id_neq: 1 } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(4, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testGreaterThan($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { id_gt: 1 } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(4, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testGreaterThanOrEquals($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { id_gte: 2 } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(4, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testLessThan($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { id_lt: 2 } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(1, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testLessThanOrEquals($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { id_lte: 2 } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(2, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testIsNull($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { isTradable_isnull:true } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(1, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testIsNotNull($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { isTradable_isnull:false } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(4, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testIn($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { id_in: [3, 4] } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(2, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testNotIn($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { id_notin: [3, 4] } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(3, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testBetween($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { id_between: { from: 2 to: 4} } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(3, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testContains($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { venue_contains: \"enue\" } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(5, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testStartsWith($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { venue_startswith: \"v\" } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(5, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testEndsWith($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { venue_endswith: \"5\" } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(1, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testSortAsc($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = '{ performance ( filter: { venue_sort:"asc" } ) { id venue performanceDate } }';

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals('venue1', $output['data']['performance'][0]['venue']);
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testSortDesc($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = '{ performance ( filter: { venue_sort:"desc" } ) { id venue performanceDate } }';

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals('venue5', $output['data']['performance'][0]['venue']);
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testSkip($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { _skip: 3 } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(2, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testLimit($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { _limit: 3 } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(3, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testOverTheLimit($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ performance ( filter: { _limit: 10000 } ) { id performanceDate } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(5, sizeof($output['data']['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testMemberOf($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ artist ( filter: { alias_memberof:\"b2\" } ) { id alias } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(['b1', 'b2', 'b3'], $output['data']['artist'][0]['alias']);
    }
}
