GraphQL for Doctrine using Hydrators
====================================

[![Build Status](https://travis-ci.org/API-Skeletons/zf-doctrine-graphql.svg)](https://travis-ci.org/API-Skeletons/zf-doctrine-graphql)
[![Coverage](https://coveralls.io/repos/github/API-Skeletons/zf-doctrine-graphql/badge.svg?branch=master&123)](https://coveralls.io/repos/github/API-Skeletons/zf-doctrine-graphql/badge.svg?branch=master&123)
[![Gitter](https://badges.gitter.im/api-skeletons/open-source.svg)](https://gitter.im/api-skeletons/open-source)
[![Total Downloads](https://poser.pugx.org/api-skeletons/zf-doctrine-graphql/downloads)](https://packagist.org/packages/api-skeletons/zf-doctrine-graphql)

This library resolves relationships in Doctrine to provide full GraphQL
querying of specified resources and all related entities.
Entity metadata is introspected and is therefore Doctrine data driver agnostic.
Data is collected via hydrators thereby
allowing full control over each field using hydrator filters and strategies.
Multiple object managers are supported.
Multiple hydrator configurations are supported.


Installation
------------

Installation of this module uses composer. For composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

```bash
$ composer require api-skeletons/zf-doctrine-graphql
```

Once installed, add `ZF\Doctrine\GraphQL` to your list of modules inside
`config/application.config.php` or `config/modules.config.php`.

> ### zf-component-installer
>
> If you use [zf-component-installer](https://github.com/zendframework/zf-component-installer),
> that plugin will install zf-doctrine-graphql as a module for you.


Use
===

```php
use Exception;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use ZF\Doctrine\GraphQL\Type\Loader as TypeLoader;
use ZF\Doctrine\GraphQL\Filter\Loader as FilterLoader;
use ZF\Doctrine\GraphQL\Resolve\Loader as ResolveLoader;

$typeLoader = $container->get(TypeLoader::class);
$filterLoader = $container->get(FilterLoader::class);
$resolveLoader = $container->get(ResolveLoader::class);

$input = $_POST;

// Context is used for configuration level variables and is optional
$context = (new Context())
    ->setLimit(1000)
    ->setHydratorSection('default')
    ->setUseHydratorCache(true)
    ;

$schema = new Schema([
    'query' => new ObjectType([
        'name' => 'query',
        'fields' => [
            'artist' => [
                'type' => Type::listOf($typeLoader(Entity\Artist::class, $context)),
                'args' => [
                    'filter' => $filterLoader(Entity\Artist::class, $context),
                ],
                'resolve' => $resolveLoader(Entity\Artist::class, $context),
            ],
            'performance' => [
                'type' => Type::listOf($typeLoader(Entity\Performance::class, $context)),
                'args' => [
                    'filter' => $filterLoader(Entity\Performance::class, $context),
                ],
                'resolve' => $resolveLoader(Entity\Performance::class, $context),
            ],
        ],
    ]),
]);

$query = $input['query'];
$variableValues = $input['variables'] ?? null;

try {
    $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context, $variableValues);
    $output = $result->toArray();
} catch (Exception $e) {
    $output = [
        'errors' => [[
            'exception' => $e->getMessage(),
        ]]
    ];
}

echo json_encode($output);
```


Running Queries
===============

An example query using the included filtering:

Fetch at most 100 performances in CA for each artist with 'Dead' in their name.

```php
$query = "{ artist ( filter: { name_contains: \"Dead\" } ) { name performance ( filter: { _limit: 100 state:\"CA\" } ) { performanceDate venue } } }";
```

Filtering
---------

For each field, which is not a reference to another entity, a colletion of filters exist.
Given an entity which contains a `name` field you may directly filter the name using
```js
filter: { name: "Grateful Dead" }
```

You may only use each field's filter once per filter action.

**Provided Filters**
```
fieldName_eq         -  Equals; same as name: value.  DateTime not supported.
fieldName_neq        -  Not Equals
fieldName_gt         -  Greater Than
fieldName_lt         -  Less Than
fieldName_gte        -  Greater Than or Equal To
fieldName_lte        -  Less Than or Equal To
fieldName_in         -  Filter for values in an array
fieldName_notin      -  Filter for values not in an array
fieldName_between    -  Fiilter between `from` and `to` values.  Good substitute for DateTime Equals.
fieldName_contains   -  Strings only. Similar to a Like query as `like '%value%'`
fieldName_startswith -  Strings only. A like query from the beginning of the value `like 'value%'`
fieldName_endswith   -  Strings only. A like query from the end of the value `like '%value'`
fieldName_isnull     -  Takes a boolean.  If TRUE return results where the field is null.
                          If FALSE returns results where the field is not null.
                          NOTE: acts as "isEmpty" for collection filters.  A value of false will
                          be handled as though it were null.
fieldName_sort       -  Sort the result by this field.  Value is 'asc' or 'desc'
fieldName_distinct   -  Return a unique list of fieldName.  Only one distinct fieldName allowed per filter.
```

The format for using these filters is:
```js
filter: { name_endswith: "Dead" }
```

For isnull the paramter is a boolean
```js
filter: { name_isnull: false  }
```

For in and notin an array of values is expected
```
filter: { name_in: ["Phish", "Legion of Mary"] }
```

For the between filter two parameters are necessary.  This is very useful for date ranges and number queries.
```
filter: { year_between: { from: 1966 to: 1995 } }
```

To select a distinct list of years
```
{ artist ( filter: { id:2 } ) { performance( filter: { year_distinct: true year_sort: "asc" } ) { year } } }
```

All filters are AND filters.  For OR support use multiple aliases queries and aggregate them.
TODO:  Add `orx` and `andx` support


Pagination
----------

The filter supports `_skip` and `_limit`.  There is a configuration
variable to set the max limit size and anything under this limit is
valid.  To select a page of data set the `_skip:10 _limit:10` and
increment `_skip` by the `_limit` for each request.  These pagination
filters exist for filtering collections too.


Configuration
=============

This module uses hydrators to extract data from the Doctrine entities.  You can configure multiple
sections of hydrators so one permissioned user may receive different data than a different permission
or one query to an entity may return differnet fields than another query to the same entity.

Because creating hydrator configurations for every section for every entity in your object manager(s) is tedious
this module provides an auto-generating configuration tool.

To generate configuration:

```sh
php public/index.php graphql:config-skeleton [--hydrator-sections=] [--object-manager=]
```

The hydrator-sections parameter is a comma delimited list of sections to generate such as `default,admin`.

The object-manager parameter is optional and defaults to `doctrine.entitymanager.orm_default`.
For each object manager you want to serve data with in your application create a configuration using this
tool.  The tool outputs a configuration file.  Write the file to your project root location then move
it to your `config/autoload` directory.

```sh
php public/index.php graphql:config-skeleton > zf-doctrine-graphql-orm_default.global.php
mv zf-doctrine-graphql-orm_default.global.php config/autoload
```

(Writing directly into the `config/autoload` directory is not recommended at run time.)

Default hydrator strategies and filters are set for every association and field in your ORM.
Modify each hydrator configuration section with your hydrator strategies and hydrator filters as needed.


Context
-------

The `Context` object provided enables configuration of GraphQL through the following options:

* limit - Set a maximum limit of each data section in a query
* hydratorSection - Which section within the hydrator configuration should be used
* useHydratorCache - By default all hydrator operations are not cached.  Enabling this value will
                     cache all the hydrator operation in anticipation that the result may be reused.


useHydratorCache Context Option
-------------------------------

The hydrator cache by defaults stores only the most recent hydrator extract data in anticipation that the next
call to the
[FieldResolver](https://github.com/API-Skeletons/zf-doctrine-graphql/blob/master/src/Field/FieldResolver.php)
will be the same object and the cache can be used.  If the same object is not requesed
for extraction then the cache is flushed and the new result is cached.

For a query
```
{ artist ( filter: { id: 2 } ) { performance { performanceDate } } }
```
All performance dates for the artist 2 will be returned.  Internally each performance is extracted according to the
hydrator filters and strategies assigned to the hydrator section and entity.  This may be many more fields than just
performanceDate.  And since we are only interested in one value setting useHydratorCache to false will flush the cache
with each new object so once a performanceDate is read and the next performance is sent to the
[FieldResolver](https://github.com/API-Skeletons/zf-doctrine-graphql/blob/master/src/Field/FieldResolver.php)
the previous hydrator extract data is purged.

For a query
```
{ performance ( filter: { id:1 } ) { performanceDate set1 set2 artist { name } set3 } }
```
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

`{ artist { user { role { user { name } } } } }`

This query would return all user names who share the same role permissions as the user who created the artist.
To prevent this the `graphql:config-skeleton` command nullifies the owning side of many to many relations by
default causing an error when the query tries to go from role > user but not when it goes from user > role
becuase role is the owning side of the many to many relationship.  See
[NullifyOwningAssociation](https://github.com/API-Skeletons/zf-doctrine-graphql/blob/master/src/Hydrator/Strategy/NullifyOwningAssociation.php)
for more information.


Supported Data Types
--------------------

This module would like to support all datatypes representable in a GraphQL response.  At this time these data types are
supported:

```
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
```

Dates are handled as ISO 8601 e.g. `2004-02-12T15:19:21+00:00`

If you have need to support a datatype not listed here please create an issue on the github project.


Provided Tools
--------------

There are three tools this library provides to help you build your GraphQL Schema.

* **TypeLoader** - This tool creates a GraphQL type for a top-level entity and all related entities beneath it.  It also creates resolvers for related collections using the [api-skeletons/zf-doctrine-criteria](https://github.com/API-Skeletons/zf-doctrine-criteria) library.
* **FilterLoader** - This tool creates filters for all non-related fields (collections) such as strings, integers, etc.  These filters are built from the [zfcampus/zf-doctrine-querybuilder](https://github.com/zfcampus/zf-doctrine-querybuilder) library.
* **ResolveLoader** - This tool builds the querybuilder object and queries the database based on the FilterLoader filters.

Each of these tools takes a fully qualified entity name as a paramter allowing you to create a top level GraphQL query field for any entity.

There is not a tool for mutations.  Those are left to the developer to build.


Events
======

Filtering Query Builders
------------------------

Each top level entity to query uses a QueryBuilder object.  This QueryBuilder object should be modified to filter
the data for the logged in user.  This is the security layer.
QueryBuilders are built then triggered through an event.  Listen to this event and modify the passed QueryBuilder to
apply your security.  The queryBuilder already has the entityClassName assigned to fetch with the alias 'row'.

Three parameters are passed in the FILTER_QUERY_BUILDER event: objectManager, entityClassName, queryBuilder.

```php
use ZF\Doctrine\GraphQL\Resolve\EntityResolveAbstractFactory;

$events = $container->get('SharedEventManager');

$events->attach(
    EntityResolveAbstractFactory::class,
    EntityResolveAbstractFactory::FILTER_QUERY_BUILDER,
    function(Event $event)
    {
        switch ($event->getParam('entityClassName')) {
            case 'Db\Entity\Performance':
                // Modify the queryBuilder for your needs
                $event->getParam('queryBuilder')
                    ->andWhere('row.id = 1')
                    ;
                break;
            default:
                break;
        }
    },
    100
);
```


Resolve
-------

The `EntityResolveAbstractFactory::RESOLVE` event includes the paramters
and allows you to override the whole ResolveLoader event.  This allows
you to have custom parameters and act on them through the ResolveLoader RESOLVE event.


Resolve Post
------------

The `EntityResolveAbstractFactory::RESOLVE_POST` event allows you to modify the values
returned from the ResolveLoader via an ArrayObject or replace the values.
