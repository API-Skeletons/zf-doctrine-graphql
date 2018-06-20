<?php

namespace ZFTest\Doctrine\GraphQL\GraphQL;

use ZFTest\Doctrine\GraphQL\AbstractTest;
use GraphQL\GraphQL;
use Db\Entity;

class ArtistTest extends AbstractTest
{
    public function testArtistEntity()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ artist { id name createdAt } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(5, sizeof($output['data']['artist']));
        }
    }

    public function testArtistPerformanceOneToMany()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ artist ( filter: { id: 1 } ) { id performance { id } } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(5, sizeof($output['data']['artist'][0]['performance']));
        }
    }

    public function testArtistUserManyToManyIsBlockedBecauseArtistIsOwner()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $query = "{ artist { id user { id } } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEmpty($output['data']['artist'][0]['user']);
        }
    }
}
