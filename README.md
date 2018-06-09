zf-doctrine-graphql
===================

[![Build Status](https://travis-ci.org/API-Skeletons/zf-doctrine-graphql.svg)](https://travis-ci.org/API-Skeletons/zf-doctrine-graphql)
[![Gitter](https://badges.gitter.im/api-skeletons/open-source.svg)](https://gitter.im/api-skeletons/open-source)
[![Total Downloads](https://poser.pugx.org/api-skeletons/zf-doctrine-graphql/downloads)](https://packagist.org/packages/api-skeletons/zf-doctrine-graphql)

This library resolves relationships in Doctrine to provide full GraphQL
querying of specified resources.  Data is collected via hydrators thereby
allowing full control over each field using hydrator filters and strategies.

Multiple object managers are supported.

** In development ** This is not recommended for anything other than experimental use.


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

Every entity within the tree of data you will serve through GraphQL must have a Hydrator Configuration.
A tool is included to create a skeleton hydrator configuration for you.

```sh
php public/index.php graphql:hydrator:config-skeleton [--object-manager=]
```

The object-manager parameter is optional and defaults to `doctrine.entitymanager.orm_default`.
For each object manager you want to serve data with in your application create a configuration using this
tool.  The tool outputs a configuration file.  Write the file to your project root location then move
it to your `config/autoload` directory.

```sh
php public/index.php graphql:hydrator:config-skeleton > zf-doctrine-graphql-default.global.php
mv zf-doctrine-graphql-default.global.php config/autoload
```

(Writing directly into the `config/autoload` directory is not recommended at run time.)

Modify each configuration with your hydrator strategies and hydrator filters as needed.


Type Casting Entity Values
--------------------------

There are some hydrator stragegies included with this module.  In GraphQL types are very important and this module
introspects your ORM metadata to correctly type against GraphQL types.  However your entities probably don't hydrate
themselves with correct PHP datatypes such as an integer will be represented in your entity as a string.  To correct
this use the included Hydrator Strategies to type cast each field.


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

* TypeLoader - This tool creates a GraphQL type for a top-level entity and all related entities beneath it.  It also creates resolvers for related collections using the [api-skeletons/zf-doctrine-criteria](https://github.com/API-Skeletons/zf-doctrine-criteria) library.
* FilterLoader - This tool creates filters for all non-related fields (collections) such as strings, integers, etc.  These filters are built from the [zfcampus/zf-doctrine-querybuilder](https://github.com/zfcampus/zf-doctrine-querybuilder) library.
* Resolve Loader - This tool builds the querybuilder object and queries the database based on the FilterLoader filters.

Each of these tools takes a fully qualified entity name as a paramter allowing you to create a top level GraphQL query field for any entity.

There is not a tool for mutations.  Those are left to the developer to build.


Use
---

Create a new RPC controller

```php
use Exception;
use GraphQL\GraphQL;
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ContentNegotiation\ViewModel as ContentNegotiationViewModel;
use ZF\Doctrine\GraphQL\Type\Loader as TypeLoader;
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
            $result = GraphQL::executeQuery($schema, $query);
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


Filtering Top Level Resources
-----------------------------

For each field, which is not a reference to another entity, a colletion of filters exist.
Given an entity which contains a `name` field you may directly filter the name using
```js
filter: { name: "Grateful Dead" }
```

You may also use any of the following:
```
name_eq        -  Equals; same as name: value
name_neq       -  Not Equals
name_gt        -  Greater Than
name_lt        -  Less Than
name_gte       -  Greater Than or Equal To
name_lte       -  Less Than or Equal To
name_isnull    -  Return results where the name field is null
name_isnotnull -  Return results where the name field is not null
name_in        -  Filter for values in an array
name_notin     -  Filter for values not in an array
name_between   -  Fiilter between `from` and `to` values
name_like      -  Use a fuzzy search
```

Every field which can be returned from a Top Level Entity has all of the above filters as field_filter.
You may only use each field's filter once per filter action.


eq, neq, gt, lt, gte, lte
-------------------------
These filters all require a single argument, `value` such as
```js
filter: { name_neq: { value: "Dave Matthews Band" } }
```

isnull, isnotnull
-----------------
These filters require no arguments and are expressed as
```js
filter: { name_isnull: {} }
```

in, notin
---------
These filters take an array of values
```
filter: { name_in: { values: ["Phish", "Legion of Mary"] } }
```

between
-------
This filter takes two parameters and is very useful for date ranges and number queries.
```
filter: { year_between: { from: 1966 to: 1995 } }
```

like
----
This filter is for strings only and allows fuzzy searching using a `%` wildcard (sql injection safe)
```
filter: { name_like: { value: "%Dead" } }
```


Optional arguments for every filter
-----------------------------------

`where` - This value defaults to 'and' and may be changed to 'or'.

todo:  Add `orx` and `andx` support and inner and left joins.


Pagination
----------

The filter supports `_skip` and `_limit`.  There is a configuration
variable to set the max limit size and anything under this limit is
valid.  To select a page of data set the `_skip:10 _limit:10` and
increment `_skip` by the `_limit` for each request.


Debugging Filters
-----------------

With so many filter options, 12 x #ofFields + 2, it can get confusing what your query looks like
so there is a `_debug` filter field which will output only the DQL version of your query.
```js
filter: { _debug:true name:"Grateful Dead" }
```
will output similar to
```sql
SELECT row FROM Db\Entity\Artist row WHERE row.name = :a5b1619627f9c1
```



Filtering Collections
---------------------

Within ORM an entity may have relationships to other entities.  For `One to One` and `Many To One`
relationships there is only one related entity and you cannot filter on this entity.  However for
`One to Many` and `Many to Many` you can filter the collection.

In this example we fetch all artists like 'Dead %' then filter their performances to year 2017 and
order them by performanceDate.
```
{
    artist( filter: { name_like:{ value: \"Dead &%\" } } ) {
        id
        name
        performance ( filter: { year: 2017 performanceDate_orderby:\"desc\" } ) {
            performanceDate year venue city state
        }
    }
}
```

The artist filter is a Top Level Resource.  The performance is a collection related to the artist and can
be filtered using these filters from [api-skeletons/zf-doctrine-criteria](https://github.com/api-skeletons/zf-doctrine-criteria) (see the [README.md](https://github.com/API-Skeletons/zf-doctrine-criteria/blob/master/README.md) for specifics):


Equals
------

`field: "value"` or `field_eq: { value: "value" }`


Not Equals
----------

`field_neq: { value: "value" }`


Less Than
---------

`field_lt: { value: "value" }`


Less Than or Equals
-------------------

`field_lte: { value: "value" }`


Greater Than
------------

`field_gt: { value: "value" }`


Greater Than or Equals
----------------------

`field_gte: { value: "value" }`


Contains
--------

> This is used for text fields to look for a sequence anywhere within the string
> Similar to Starts With and Ends With

`field_contains: { value: "value" }`


Starts With
-----------

`field_startswith: { value: "value" }


Ends With
---------

`field_endswith: { value: "value" }`


In
-----

> Note the parameter is plural `values`

`field_in: { values: [1, 2, 3] }`


Not In
------

> Note the parameter is plural `values`

`field_notin: { values: [1, 2, 3] }`


Order By
--------

Used for sorting a collection.  Valid values are 'asc' and 'desc'

`field_orderby: "asc"`
