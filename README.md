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
php public/index.php graphql:hydrator:config-skeleton > zf-doctrine-graphql-hydrator-default.global.php
mv zf-doctrine-graphql-hydrator-default.global.php config/autoload
```

(Writing directly into the `config/autoload` directory is not possible at run time.)

Modify each configuration with your hydrator strategies and hydrator filters as needed.


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
                            'id' => Type::id(),
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

Filters
-------

For each field which is not a reference to another entity a colletion of filters exist.
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

Every field which can be returned from an Entity has all of the above filters as field_filter.
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
These filters take an array of arguments
```
filter: { name_in: { value: ["Phish", "Legion of Mary"] } }
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
`alias` - The parent alias.  Defaults to `row` which is the alias for the root entity being queried.
Other values for `alias` are not supported at this time.


todo:  Add `orx` and `andx` support and inner and left joins.
