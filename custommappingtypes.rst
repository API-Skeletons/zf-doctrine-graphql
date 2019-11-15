Custom Mapping Types
====================

Doctrine allows
`Custom Mapping Types <https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/cookbook/custom-mapping-types.html>`_

You must create a custom GraphQL type for the field for handling serialization,
etc.  See **ZF\\Doctrine\\GraphQL\\Type\\DateTimeType** for an example of a
custom GraphQL type.

Add the new custom GraphQL type to your configuration::

    'zf-doctrine-graphql-type' => [
        'invokables' => [
            'datetime_microsecond'
                => Types\GraphQL\DateTimeMicrosecondType::class,
        ],
    ],


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
