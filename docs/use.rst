Use
===

This example merges work from a factory into the example.  Moving the `$container` calls to a factory
and injecting them into an RPC object will yield a working example.

.. highlight:: php

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
