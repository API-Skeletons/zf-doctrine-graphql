<?php

namespace ZFTest\Doctrine\GraphQL\GraphQL;

use ZFTest\Doctrine\GraphQL\AbstractTest;
use ZF\Doctrine\GraphQL\Context;

class ContextTest extends AbstractTest
{
    public function testContextObjectDefaults()
    {
        $context = new Context();

        $this->assertEquals(1000, $context->getLimit());
        $this->assertEquals('default', $context->getHydratorSection());
        $this->assertEquals(false, $context->getUseHydratorCache());
    }

    public function testContextObjectCustom()
    {
        $context = new Context();
        $context->setHydratorSection('test');
        $context->setUseHydratorCache(true);
        $context->setLimit(2000);

        $this->assertEquals(2000, $context->getLimit());
        $this->assertEquals('test', $context->getHydratorSection());
        $this->assertEquals(true, $context->getUseHydratorCache());
    }
}
