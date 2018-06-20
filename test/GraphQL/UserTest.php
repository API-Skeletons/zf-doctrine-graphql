<?php

namespace ZFTest\Doctrine\GraphQL\GraphQL;

use ZFTest\Doctrine\GraphQL\AbstractTest;
use GraphQL\GraphQL;
use Db\Entity;

class UserTest extends AbstractTest
{
    public function testUserEntity()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ user { id name } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(5, sizeof($output['data']['user']));
        }
    }

    public function testUserFilterId()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ user (filter: { id:1 }) { id name } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(1, sizeof($output['data']['user']));
        }
    }

    public function testUserAddress()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ user (filter: { id:1 }) { id name address { id address } } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals('address1', $output['data']['user'][0]['address']['address']);
        }
    }

    public function testPasswordFilter()
    {
        foreach ($this->schemaDataProvider() as $schemaName => $schema) {
            $query = "{ user (filter: { id:1 }) { password } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(
                'Cannot query field "password" on type "DbTest\Entity\User_' . $schemaName . '".',
                $output['errors'][0]['message']
            );
        }
    }

    public function testUserArtistManyToManyWorksBecauseArtistIsOwner()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ user( filter: { id:1 } ) { id artist { id } } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(5, sizeof($output['data']['user'][0]['artist']));
        }
    }

    public function testFetchCriteriaForRelation()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ user( filter: { id:1 } ) { id artist { id performance ( filter: { id: 3 } ) { id } } } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(5, sizeof($output['data']['user'][0]['artist']));
        }
    }
}
