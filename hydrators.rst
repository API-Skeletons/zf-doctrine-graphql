Hydrator Configuration
======================

Even deveopers who have used Doctrine or an ORM a lot may not have experience
with hydrators.  This section is to educate and help the developer understand
hydrators and how to use them in relation to Doctrine ORM and GraphQL.

A hydrator moves data into and out of an object as an array.  The array may
contain scalar values, arrays, and closures. For instance, if you have an
entity with the fields::

    id
    name
    description

a hydrator can create an array from your entity resulting in::

    $array['id']
    $array['name']
    $array['description']

The `Zend Framework documentaion on Hydrators <https://framework.zend.com/manual/2.4/en/modules/zend.stdlib.hydrator.html>`_
is a good read for background about coding hydrators from scratch.

The `Doctrine Hydrator documentation <https://github.com/doctrine/DoctrineModule/blob/master/docs/hydrator.md>`_
is more complete and more pertinent to this repository.  A notable section is
`By Value and By Reference <https://github.com/doctrine/DoctrineModule/blob/master/docs/hydrator.md#by-value-and-by-reference>`_


Generating a Skeleton Configuration
-----------------------------------

This module uses hydrators to extract data from the Doctrine entities.  You
can configure multiple sections of hydrators so one permissioned user may
receive different data than a different permission or one query to an entity
may return different fields than another query to the same entity.

Because creating hydrator configurations for every section for every entity
in your object manager(s) is tedious this module provides an auto-generating
configuration tool.

To generate configuration:

.. code-block:: bash

    php public/index.php graphql:config-skeleton [--hydrator-sections=] [--object-manager=]


The hydrator-sections parameter is a comma delimited list of sections to
generate such as `default,admin`.

The object-manager parameter is optional and defaults to
`doctrine.entitymanager.orm_default`.  For each object manager you want to
serve data with in your application create a configuration using this
tool.  The tool outputs a configuration file.  Write the file to your project
root location then move it to your `config/autoload` directory.

.. code-block:: bash

    php public/index.php graphql:config-skeleton > zf-doctrine-graphql-orm_default.global.php
    mv zf-doctrine-graphql-orm_default.global.php config/autoload


(Writing directly into the `config/autoload` directory is not recommended at
run time.)

Default hydrator strategies and filters are set for every association and field
in your ORM.  Modify each hydrator configuration section with your hydrator
strategies and hydrator filters as needed.


Generated Configuration
-----------------------

Here is an example of a generated configuration::

    'ZF\\Doctrine\\GraphQL\\Hydrator\\ZF_Doctrine_Audit_Entity_Revision' => [
        'default' => [
            'entity_class' => \ZF\Doctrine\Audit\Entity\Revision::class,
            'object_manager' => 'doctrine.entitymanager.orm_zf_doctrine_audit',
            'by_value' => true,
            'use_generated_hydrator' => true,
            'naming_strategy' => null,
            'hydrator' => null,
            'strategies' => [
                'id' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                'comment' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                'connectionId' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                'createdAt' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                'userEmail' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                'userId' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                'userName' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                'revisionEntity' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
            ],
            'filters' => [
                'default' => [
                    'condition' => 'and',
                    'filter' => \ZF\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                ],
            ],
            'documentation' => [
                '_entity' => '',
                'id' => '',
                'comment' => '',
                'connectionId' => '',
                'createdAt' => '',
                'userEmail' => '',
                'userId' => '',
                'userName' => '',
            ],
        ],
    ],

The `entity_class` is the fully qualified entity class name this
configuration section is for.

The `object_manager` is the service manager alias for the object manager
which manages the `entity_class`.

`by_value` is an important switch.  When set to `true` the values for the
entity will be fetched using their getter methods such as `getName()` for a
`name` field.  When set to `false` the entity will be Reflected and the
property value of the entity class will be extracte `by reference`.  If your
entities are not extracting properly try toggling this value.

`by_value` set to `false` is useful when your entity does not have getter
methods such as a dynamically created entity.
`API-Skeletons/zf-doctrine-audit <https://github.com/API-Skeletons/zf-doctrine-audit>`_
is a good example for this.  The dynamically generated auditing entities do
not have getter methods but do have properties to contain the field values.
These can be extracted `by reference`.

`use_generated_hydrator` is usually set to true.  Because GraphQL uses
hydrators for extraction only this value is not used.  But if you want to
use the same configured hydrators to hydrate an entity please see the code
for its use.

`hydrator` allows complete overriding of the extract service.  If set the
extract and hydrate services will be assigned to the specified hydrator.

`naming_strategy` is an instance of
**Zend\Hydrator\NamingStrategy\NamingStrategyInterface** and is a service
manager alias.  You may only have one `naming_strategy` per hydrator
configuration.  A naming strategy lets you rename fields.

`strategies` are quite important for extracting entities.  These can change
the extracted value in whatever way you wish such as `rot13()` email addresses.
They can return an empty value but for that case it's better to filter out the
field completely.

`filters` are toggle switches for fields.  If you return false for a field
name it will remove the field from the extract result.

`documentation` section is for fields only.  Relations are not documented
because that is not supported by GraphiQL.  Use this section to document
each field and the entity.  The reserved name `_entity` contains the
documentation for the entity.


Strategies
----------

There are some hydrator stragegies included with this module.  In GraphQL
types are very important and this module introspects your ORM metadata to
correctly type against GraphQL types.  By default integer, float, and
boolean fields are automatically assigned to the correct hydrator strategy.


Many to Many Owning Side Relationships
--------------------------------------

.. code-block:: js

    { artist { user { role { user { name } } } } }


This query would return all user names who share the same role permissions as
the user who created the artist.  To prevent this the `graphql:config-skeleton`
command nullifies the owning side of many to many relations by default causing
an error when the query tries to go from role > user but not when it goes from
user > role becuase role is the owning side of the many to many relationship.
See
`NullifyOwningAssociation <https://github.com/API-Skeletons/zf-doctrine-graphql/blob/master/src/Hydrator/Strategy/NullifyOwningAssociation.php>`_
for more information.


Documenting Entities
--------------------

Introspection of entities is a core component to GraphQL.  The introspection
allows you to document your types.  Because entities are types there is a
section inside each hydrator configuration for documenting your entity and
fields through introspection.

.. code-block:: php

    'documentation' => [
        '_entity' => 'The Artist entity contains bands, groups, and individual performers.',
        'id' => 'Primary Key for the Artist',
        'abbreviation' => 'An abbreviation for the Artist',
        'createdAt' => 'DateTime the Artist record was created',
        'description' => 'A description of the Artist',
        'icon' => 'The Artist icon',
        'name' => 'The name of the performer.',
    ],

There is one special field, `_entity` which is the description for the entity
itself.  The rest of the fields describe documentation for each field.

Documentation is specific to each hydrator section allowing you to describe
the same entity in different ways.  The Documentation will be returned in
tools like `GraphiQL <https://github.com/graphql/graphiql>`_

GraphiQL is the standard for browsing introspected GraphQL types.
zf-doctrine-graphql fully supports GraphiQL.


.. role:: raw-html(raw)
   :format: html

.. note::
  Authored by `API Skeletons <https://apiskeletons.com>`_.  All rights reserved.
