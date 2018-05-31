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

You will need to create hydrators for every entity you wish to serve data for.
You will need to list the alises for each object manager you wish to serve data for.


Use
---

Create a new RPC controller
```
use Exception
use GraphQL\GraphQL;
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ContentNegotiation\ViewModel as ContentNegotiationViewModel;
use ZF\Doctrine\GraphQL\Type\Loader as TypeLoader;
use ZF\Doctrine\GraphQL\Resolve\Loader as ResolveLoader;
use Db\Entity;

class GraphQLController extends AbstractActionController
{
    private $typeLoader;
    private $resolveLoader;

    public function __construct(TypeLoader $typeLoader, ResolveLoader $resolveLoader)
    {
        $this->typeLoader = $typeLoader;
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
                        'type' => $typeLoader(Entity\Artist::class),
                        'args' => [
                            'id' => Type::id(),
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
