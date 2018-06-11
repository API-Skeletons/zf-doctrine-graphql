zf-doctrine-graphql
===================

[![Build Status](https://travis-ci.org/API-Skeletons/zf-doctrine-graphql.svg)](https://travis-ci.org/API-Skeletons/zf-doctrine-graphql)
[![Gitter](https://badges.gitter.im/api-skeletons/open-source.svg)](https://gitter.im/api-skeletons/open-source)
[![Total Downloads](https://poser.pugx.org/api-skeletons/zf-doctrine-graphql/downloads)](https://packagist.org/packages/api-skeletons/zf-doctrine-graphql)

This library resolves relationships in Doctrine to provide full GraphQL
querying of specified resources.  Entity metadata is introspected and is
therefore Doctrine data driver agnostic.
Data is collected via hydrators thereby
allowing full control over each field using hydrator filters and strategies.
Multiple object managers are supported.  This library enables queries only.
Producing mutations is left up to the developer.


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


Configuration
-------------

Because creating hydrator configurations for every entity in your object manager(s) is tedious
this module provides an auto-generating configuration tool.

There are three sections to the generated configuration:

* `zf-doctrine-graphql`: An array of configuration options.  The option(s) are
  * `limit`: The maximum number of results to return for each entity or collection.

* `zf-doctrine-graphql-query-provider`: This allows you to provide security to your top level entity queries.  For each top level entity query a query provider is required.

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

Default hydrator strategies and filters are provided for every association and field in your ORM.
Modify each hydrator configuration with your hydrator strategies and hydrator filters as needed.


Type Casting Entity Values
--------------------------

There are some hydrator stragegies included with this module.  In GraphQL types are very important and this module
introspects your ORM metadata to correctly type against GraphQL types.  By default integer, float, and boolean fields are automatically assigned to the correct hydrator strategy.


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

If you have need to support a datatype not listed here please create an issue on the github project.


Provided Tools
--------------

There are three tools this library provides to help you build your GraphQL Schema.

* **TypeLoader** - This tool creates a GraphQL type for a top-level entity and all related entities beneath it.  It also creates resolvers for related collections using the [api-skeletons/zf-doctrine-criteria](https://github.com/API-Skeletons/zf-doctrine-criteria) library.
* **FilterLoader** - This tool creates filters for all non-related fields (collections) such as strings, integers, etc.  These filters are built from the [zfcampus/zf-doctrine-querybuilder](https://github.com/zfcampus/zf-doctrine-querybuilder) library.
* **ResolveLoader** - This tool builds the querybuilder object and queries the database based on the FilterLoader filters.

Each of these tools takes a fully qualified entity name as a paramter allowing you to create a top level GraphQL query field for any entity.

There is not a tool for mutations.  Those are left to the developer to build.


Query Providers
---------------

Each top level entity to query requires a Query Provider.  The configuration section `zf-doctrine-graphql-query-provider` is a service manager configuration for your Query Providers.  This is where to implement security for
your data.  It is expected that related data to a top level entity is allowable for the user.  If there is data
which a user should not be able to get to adjust your hydrator filters to disallow querying that relation.

Example Configuration for one top level Artist entity:
```php
    'zf-doctrine-graphql-query-provider' => [
        'aliases' => [
            \Db\Entity\Artist::class => \GraphQLApi\QueryProvider\Artist::class,
        ],
        'invokables' => [
            \GraphQLApi\QueryProvider\Artist::class => \GraphQLApi\QueryProvider\Artist::class,
        ],
    ],
```

Example Query Provider:
```php
namespace GraphQLApi\QueryProvider;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use ZF\Doctrine\GraphQL\QueryProvider\QueryProviderInterface;
use Db\Entity;

final class Artist implements
    QueryProviderInterface
{
    /**
     * @param ResourceEvent $event
     * @return QueryBuilder
     */
    public function createQuery(ObjectManager $objectManager) : QueryBuilder
    {
        $queryBuilder = $objectManager->createQueryBuilder();
        $queryBuilder
            ->select('row')
            ->from(Entity\Artist::class, 'row')
            ;

        return $queryBuilder;
    }
}
```

The 'row' alias for the default entity is required to be 'row'.


Use
---

Create a new RPC controller

Controller Factory
```php
use Interop\Container\ContainerInterface;
use ZF\Doctrine\GraphQL\Type\Loader as TypeLoader;
use ZF\Doctrine\GraphQL\Filter\Loader as FilterLoader;
use ZF\Doctrine\GraphQL\Resolve\Loader as ResolveLoader;

class GraphQLControllerFactory
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        $typeLoader = $container->get(TypeLoader::class);
        $filterLoader = $container->get(FilterLoader::class);
        $resolveLoader = $container->get(ResolveLoader::class);

        return new GraphQLController($typeLoader, $filterLoader, $resolveLoader);
    }
}
```

Controller Class

```php
use Exception;
use GraphQL\GraphQL;
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ContentNegotiation\ViewModel as ContentNegotiationViewModel;
use ZF\Doctrine\GraphQL\Type\Loader as TypeLoader;
use ZF\Doctrine\GraphQL\Filter\Loader as FilterLoader;
use ZF\Doctrine\GraphQL\Resolve\Loader as ResolveLoader;
use Db\Entity;

class GraphQLController extends AbstractActionController
{
    private $typeLoader;
    private $filterLoader;
    private $resolveLoader;

    public function __construct(TypeLoader $typeLoader, FilterLoader $filterLoader, ResolveLoader $resolveLoader)
    {
        $this->typeLoader = $typeLoader;
        $this->filterLoader = $filterLoader;
        $this->resolveLoader = $resolveLoader;
    }

    public function graphQLAction()
    {
        $input = $this->bodyParams();

        $typeLoader = $this->typeLoader;
        $resolveLoader = $this->resolveLoader;

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
                        'exception' => $e->getMessage()
                ]]
            ];
        }

        return new ContentNegotiationViewModel([
            'payload' => $output,
        ]);
    }
}
```


Running Queries
===============

Filtering
---------

For each field, which is not a reference to another entity, a colletion of filters exist.
Given an entity which contains a `name` field you may directly filter the name using
```js
filter: { name: "Grateful Dead" }
```

You may only use each field's filter once per filter action.
You may also use any of the following:
```
fieldName_eq        -  Equals; same as name: value
fieldName_neq       -  Not Equals
fieldName_gt        -  Greater Than
fieldName_lt        -  Less Than
fieldName_gte       -  Greater Than or Equal To
fieldName_lte       -  Less Than or Equal To
fieldName_isnull    -  Return results where the name field is null
fieldName_isnotnull -  Return results where the name field is not null
fieldName_in        -  Filter for values in an array
fieldName_notin     -  Filter for values not in an array
fieldName_between   -  Fiilter between `from` and `to` values
fieldName_contains  -  Similar to a Like query as `like '%value%'`
fieldName_startswith - A like query from the beginning of the value `like 'value%'`
fieldName_endswith   - A like query from the end of the value `like '%value'`
fieldName_sort       - Sort the result by this field.  Value is 'asc' or 'desc'
```

The format for using these filters is:
```js
filter: { name_endswith: { value: "Dead" } }
```

For isnull and isnotnull there is no value parameter
```js
filter: { name_isnotnull: {} }
```

For in and notin an array of values is expected
```
filter: { name_in: { values: ["Phish", "Legion of Mary"] } }
```

For the between filter two parameters are necessary.  This is very useful for date ranges and number queries.
```
filter: { year_between: { from: 1966 to: 1995 } }
```


Optional arguments for every filter
-----------------------------------

`where` - This value defaults to 'and' and may be changed to 'or'.

TODO:  Add `orx` and `andx` support


Pagination
----------

The filter supports `_skip` and `_limit`.  There is a configuration
variable to set the max limit size and anything under this limit is
valid.  To select a page of data set the `_skip:10 _limit:10` and
increment `_skip` by the `_limit` for each request.  These pagination
filters exist for filtering collections too.


Debugging Filters
-----------------

With so many filter options it can get confusing what your query looks like
so there is a `_debug` filter field which will output only the DQL version of your query.
```js
filter: { _debug:true name:"Grateful Dead" }
```
will output similar to
```sql
SELECT row FROM Db\Entity\Artist row WHERE row.name = :a5b1619627f9c1
```
The debug filter only works for the top level resource and not collection filters.
