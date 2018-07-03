Events
======

Filtering Query Builders
------------------------

Each top level entity to query uses a QueryBuilder object.  This QueryBuilder object should be modified to filter
the data for the logged in user.  This is the security layer.
QueryBuilders are built then triggered through an event.  Listen to this event and modify the passed QueryBuilder to
apply your security.  The queryBuilder already has the entityClassName assigned to fetch with the alias 'row'.

.. code-block:: php

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


Resolve
-------

The **EntityResolveAbstractFactory::RESOLVE** event includes the **parameters**
and allows you to override the whole ResolveLoader event.  This allows
you to have custom parameters and act on them through the ResolveLoader RESOLVE event.


Resolve Post
------------

The **EntityResolveAbstractFactory::RESOLVE_POST** event allows you to modify the values
returned from the ResolveLoader via an ArrayObject or replace the values.


Override GraphQL Type
---------------------

The **EntityTypeAbstractFactory::TYPE_DEFINITION** event allows you to override the GraphQL
type for any field.  Imagine you have an **array** field on an entity and the array field
is multi-dimentional.  Because this module handles arrays as arrays of strings (because
GraphQL needs to know exact subtypes of types) it cannot handle a multi-dimentional array.
A good solution is to turn the value into JSON and override the type to a String.

.. code-block:: php

    use ZF\Doctrine\GraphQL\Type\EntityTypeAbstractFactory;

    $events = $container->get('SharedEventManager');

    $events->attach(
        EntityTypeAbstractFactory::class,
        EntityTypeAbstractFactory::TYPE_DEFINITION,
        function(Event $event)
        {
            $hydratorAlias = $event->getParam('hydratorAlias');
            $fieldName = $event->getParam('fieldName');

            if ($hydratorAlias == 'ZF\\Doctrine\\GraphQL\\Hydrator\\DbTest_Entity_Artist') {
                if ($fieldName === 'arrayField') {
                    $event->stopPropagation();

                    return Type::string();
                }
            }
        },
        100
    );



.. role:: raw-html(raw)
   :format: html

.. note::
  Authored by **API Skeletons <https://apiskeletons.com>**_.  All rights reserved.


:raw-html:**<script async src="https://www.googletagmanager.com/gtag/js?id=UA-64198835-4"></script><script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'UA-64198835-4');</script>**
