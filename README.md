zf-doctrine-graphql
===================

[![Build Status](https://travis-ci.org/API-Skeletons/zf-doctrine-graphql.svg)](https://travis-ci.org/API-Skeletons/zf-doctrine-graphql)
[![Coverage](https://coveralls.io/repos/github/API-Skeletons/zf-doctrine-graphql/badge.svg?branch=master)](https://coveralls.io/repos/github/API-Skeletons/zf-doctrine-graphql/badge.svg?branch=master)
[![Gitter](https://badges.gitter.im/api-skeletons/open-source.svg)](https://gitter.im/api-skeletons/open-source)
[![Total Downloads](https://poser.pugx.org/api-skeletons/zf-doctrine-graphql/downloads)](https://packagist.org/packages/api-skeletons/zf-doctrine-graphql)

This library resolves relationships in Doctrine to provide full GraphQL
querying of specified resources and all related entities.
Entity metadata is introspected and is therefore Doctrine data driver agnostic.
Data is collected via hydrators thereby
allowing full control over each field using hydrator filters and strategies.
Multiple object managers are supported.  This library enables queries only.
Producing mutations is left to the developer.


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

$schema = new Schema([
    'query' => new ObjectType([
        'name' => 'query',
        'fields' => [
            'artist' => [
                'type' => Type::listOf($typeLoader(Entity\Artist::class)),
                'args' => [
                    'filter' => $filterLoader(Entity\Artist::class),
                ],
                'resolve' => $resolveLoader(Entity\Artist::class),
            ],
        ],
    ]),
]);

$query = $input['query'];
$variableValues = isset($input['variables']) ? $input['variables'] : null;

try {
    $result = GraphQL::executeQuery($schema, $query, $rootValue = null, $context = null, $variableValues);
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

All filters are AND filters.
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

Because creating hydrator configurations for every entity in your object manager(s) is tedious
this module provides an auto-generating configuration tool.

There are two sections to the generated configuration:

* `zf-doctrine-graphql`: An array of configuration options.  The option(s) are
  * `limit`: The maximum number of results to return for each entity or collection.

* `zf-doctrine-graphql-hydrator`: An array of hydrator configurations.  Every entity within the tree of data you will serve through GraphQL must have a Hydrator Configuration.

To generate configuration:

```sh
php public/index.php graphql:config-skeleton [--object-manager=]
```

The object-manager parameter is optional and defaults to `doctrine.entitymanager.orm_default`.
For each object manager you want to serve data with in your application create a configuration using this
tool.  The tool outputs a configuration file.  Write the file to your project root location then move
it to your `config/autoload` directory.

```sh
php public/index.php graphql:hydrator:config-skeleton > zf-doctrine-graphql-orm_default.global.php
mv zf-doctrine-graphql-orm_default.global.php config/autoload
```

(Writing directly into the `config/autoload` directory is not recommended at run time.)

Default hydrator strategies and filters are sed for every association and field in your ORM.
Modify each hydrator configuration with your hydrator strategies and hydrator filters as needed.


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

Dates are handled as ISO 8601 e.g. 2004-02-12T15:19:21+00:00

If you have need to support a datatype not listed here please create an issue on the github project.


Provided Tools
--------------

There are three tools this library provides to help you build your GraphQL Schema.

* **TypeLoader** - This tool creates a GraphQL type for a top-level entity and all related entities beneath it.  It also creates resolvers for related collections using the [api-skeletons/zf-doctrine-criteria](https://github.com/API-Skeletons/zf-doctrine-criteria) library.
* **FilterLoader** - This tool creates filters for all non-related fields (collections) such as strings, integers, etc.  These filters are built from the [zfcampus/zf-doctrine-querybuilder](https://github.com/zfcampus/zf-doctrine-querybuilder) library.
* **ResolveLoader** - This tool builds the querybuilder object and queries the database based on the FilterLoader filters.

Each of these tools takes a fully qualified entity name as a paramter allowing you to create a top level GraphQL query field for any entity.

There is not a tool for mutations.  Those are left to the developer to build.


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
