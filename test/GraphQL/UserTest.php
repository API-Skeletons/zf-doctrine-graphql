<?php

namespace ZFTest\Doctrine\GraphQL\GraphQL;

use ZFTest\Doctrine\GraphQL\AbstractTest;
use GraphQL\GraphQL;
use Db\Entity;

class UserTest extends AbstractTest
{
    public function testWorking()
    {
        $schema = $this->getSchema();

        $query = "{ user { id name } }";

        $result = GraphQL::executeQuery($schema, $query);
        $output = $result->toArray();

        $this->assertTrue(true);
    }
}
