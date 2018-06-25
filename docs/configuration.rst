Configuration
=============

This module uses hydrators to extract data from the Doctrine entities.  You can configure multiple
sections of hydrators so one permissioned user may receive different data than a different permission
or one query to an entity may return differnet fields than another query to the same entity.

Because creating hydrator configurations for every section for every entity in your object manager(s) is tedious
this module provides an auto-generating configuration tool.

To generate configuration:

.. code-block:: bash

    php public/index.php graphql:config-skeleton [--hydrator-sections=] [--object-manager=]


The hydrator-sections parameter is a comma delimited list of sections to generate such as `default,admin`.

The object-manager parameter is optional and defaults to `doctrine.entitymanager.orm_default`.
For each object manager you want to serve data with in your application create a configuration using this
tool.  The tool outputs a configuration file.  Write the file to your project root location then move
it to your `config/autoload` directory.

.. code-block:: bash

    php public/index.php graphql:config-skeleton > zf-doctrine-graphql-orm_default.global.php
    mv zf-doctrine-graphql-orm_default.global.php config/autoload


(Writing directly into the `config/autoload` directory is not recommended at run time.)

Default hydrator strategies and filters are set for every association and field in your ORM.
Modify each hydrator configuration section with your hydrator strategies and hydrator filters as needed.


Context
-------

The `Context` object provided enables configuration of GraphQL through the following options:

* limit - Set a maximum limit of each data section in a query
* hydratorSection - Which section within the hydrator configuration should be used
* useHydratorCache - By default all hydrator operations are not cached.  Enabling this value will cache all the hydrator operation in anticipation that the result may be reused.

Context is the configuration for each GraphQL entry point.  This allows unlimited configuration through
multiple hydrator sections.

You must use the same context object for a Query as you assign to the Loader.  This may be done via
different Schemas or different RPC endpoints.


useHydratorCache Context Option
-------------------------------

The hydrator cache by defaults stores only the most recent hydrator extract data in anticipation that the next
call to the
`FieldResolver <https://github.com/API-Skeletons/zf-doctrine-graphql/blob/master/src/Field/FieldResolver.php>`_
will be the same object and the cache can be used.  If the same object is not requesed
for extraction then the cache is flushed and the new result is cached.

For a query

.. code-block:: js

    { artist ( filter: { id: 2 } ) { performance { performanceDate } } }


All performance dates for the artist 2 will be returned.  Internally each performance is extracted according to the
hydrator filters and strategies assigned to the hydrator section and entity.  This may be many more fields than just
performanceDate.  And since we are only interested in one value setting useHydratorCache to false will flush the cache
with each new object so once a performanceDate is read and the next performance is sent to the
`FieldResolver <https://github.com/API-Skeletons/zf-doctrine-graphql/blob/master/src/Field/FieldResolver.php>`_
the previous hydrator extract data is purged.

For a query

.. code-block:: js

    { performance ( filter: { id:1 } ) { performanceDate set1 set2 artist { name } set3 } }


useHydratorCache set to true will cause set3 to be pulled from the cache.  If it were set to false set3 would generate
a new hydrator extract operation on an entity which had already been extracted once before.

useHydratorCache set to false will fetch set1 and set2 from the single-entity cache created by the performanceDate.


Type Casting Entity Values
--------------------------

There are some hydrator stragegies included with this module.  In GraphQL types are very
important and this module introspects your ORM metadata to correctly type against GraphQL
types.  By default integer, float, and boolean fields are automatically assigned to the
correct hydrator strategy.


Many to Many Owning Side Relationships
--------------------------------------

.. code-block:: js

    { artist { user { role { user { name } } } } }


This query would return all user names who share the same role permissions as the user who created the artist.
To prevent this the `graphql:config-skeleton` command nullifies the owning side of many to many relations by
default causing an error when the query tries to go from role > user but not when it goes from user > role
becuase role is the owning side of the many to many relationship.  See
`NullifyOwningAssociation <https://github.com/API-Skeletons/zf-doctrine-graphql/blob/master/src/Hydrator/Strategy/NullifyOwningAssociation.php>`_
for more information.


Supported Data Types
--------------------

This module would like to support all datatypes representable in a GraphQL response.  At this time these data types are
supported::

    array    - Arrays are handled as arrays of strings because Doctrine does not type the values of the array.
    tinyint
    smallint
    integer
    int
    bigint
    boolean
    decimal
    float
    string
    text
    datetime


Dates are handled as ISO 8601 e.g. `2004-02-12T15:19:21+00:00`

If you have need to support a datatype not listed here please create an issue on the github project.


Provided Tools
--------------

There are three tools this library provides to help you build your GraphQL Schema.

* **TypeLoader** - This tool creates a GraphQL type for a top-level entity and all related entities beneath it.  It also creates resolvers for related collections using the `api-skeletons/zf-doctrine-criteria <https://github.com/API-Skeletons/zf-doctrine-criteria>`_ library.
* **FilterLoader** - This tool creates filters for all non-related fields (collections) such as strings, integers, etc.  These filters are built from the `zfcampus/zf-doctrine-querybuilder <https://github.com/zfcampus/zf-doctrine-querybuilder>`_ library.
* **ResolveLoader** - This tool builds the querybuilder object and queries the database based on the FilterLoader filters.

Each of these tools takes a fully qualified entity name as a paramter allowing you to create a top level GraphQL query field for any entity.

There is not a tool for mutations.  Those are left to the developer to build.

