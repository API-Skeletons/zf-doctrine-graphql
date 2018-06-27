<?php

namespace ZFTest\Doctrine\GraphQL\GraphQL;

use ZFTest\Doctrine\GraphQL\AbstractTest;
use GraphQL\GraphQL;
use Db\Entity;

class UserTest extends AbstractTest
{
    /**
     * @dataProvider schemaDataProvider
     */
    public function testUserEntity($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ user { id name } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(5, sizeof($output['data']['user']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testUserFilterId($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);
        $query = "{ user (filter: { id:1 }) { id name } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(1, sizeof($output['data']['user']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testUserAddress($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ user (filter: { id:1 }) { id name address { id address } } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals('address1', $output['data']['user'][0]['address']['address']);
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testPasswordFilter($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ user (filter: { id:1 }) { password } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(
            'Cannot query field "password" on type "DbTest_Entity_User__' . $schemaName . '".',
            $output['errors'][0]['message']
        );
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testUserArtistManyToManyWorksBecauseArtistIsOwner($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ user( filter: { id:1 } ) { id artist { id } } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(5, sizeof($output['data']['user'][0]['artist']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testFetchCriteriaForRelation($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ user( filter: { id:1 } ) { id artist { id performance ( filter: { id: 3 } ) { id } } } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(5, sizeof($output['data']['user'][0]['artist']));
    }
}
