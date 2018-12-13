Use
===

This example merges work from a factory into the example.  Moving the `$container` calls to a factory
and injecting them into an RPC object will yield a working example.::

    use Exception;
    use GraphQL\GraphQL;
    use GraphQL\Type\Schema;
    use GraphQL\Type\Definition\Type;
    use GraphQL\Type\Definition\ObjectType;
    use ZF\Doctrine\GraphQL\Type\Loader as TypeLoader;
    use ZF\Doctrine\GraphQL\Filter\Loader as FilterLoader;
    use ZF\Doctrine\GraphQL\Resolve\Loader as ResolveLoader;
    use ZF\Doctrine\GraphQL\Context;

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
        // Context in the `executeQuery` is required.  If you do not assign a specific context as shown
        // you still need to send a `new Context()` to `executeQuery`.
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


.. role:: raw-html(raw)
   :format: html

.. note::
  Authored by `API Skeletons <https://apiskeletons.com>`_.  All rights reserved.


:raw-html:`<script async src="https://www.googletagmanager.com/gtag/js?id=UA-64198835-4"></script><script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'UA-64198835-4');</script>`
