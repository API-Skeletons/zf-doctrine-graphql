<?php

namespace ZFTest\Doctrine\GraphQL\GraphQL;

use ZFTest\Doctrine\GraphQL\AbstractTest;
use GraphQL\GraphQL;
use Db\Entity;

class ArtistTest extends AbstractTest
{
    /**
     * @dataProvider schemaDataProvider
     */
    public function testArtistEntity($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ artist { id name createdAt } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(5, sizeof($output['data']['artist']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testArtistPerformanceOneToMany($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ artist ( filter: { id: 1 } ) { id performance { id } } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(5, sizeof($output['data']['artist'][0]['performance']));
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testArtistUserManyToManyIsBlockedBecauseArtistIsOwner($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ artist { id user { id } } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEmpty($output['data']['artist'][0]['user']);
    }

    /**
     * @dataProvider schemaDataProvider
     */
    public function testArtistAliasArrayField($schemaName, $context)
    {
        $schema = $this->getSchema($schemaName);

        $query = "{ artist ( filter: { name:\"artist1\" } ) { id alias } }";

        $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues = null);
        $output = $result->toArray();

        $this->assertEquals(['a1', 'a2', 'a3'], $output['data']['artist'][0]['alias']);
    }
}
