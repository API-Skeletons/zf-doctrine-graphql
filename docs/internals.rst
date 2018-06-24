Internals
=========


Hydrator Extract Tool
---------------------

All hydrator extract operations are handled through the Hydrator Extract Tool.  This tool is engineered to be overridden
thanks to a service manager alias.  Should you find the need to add custom caching to hydrator results this is where to
do it.  To register a custom hydrator extract tool use

.. code-block:: php

    'aliases' => [
        'ZF\Doctrine\GraphQL\Hydrator\HydratorExtractTool' => CustomExtractTool::class,
    ],



Field Resolver
--------------

This standard part of GraphQL resolves individual fields and is where the built in caching resides.  This resolver uses
the Hydrator Extract Tool and returns one field value at a time.  For high performance writing your own Field Resolver is an
option.  To register a custom field resolver use `GraphQL::setDefaultFieldResolver($fieldResolver);`
