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

The `EntityResolveAbstractFactory::RESOLVE` event includes the `parameters`
and allows you to override the whole ResolveLoader event.  This allows
you to have custom parameters and act on them through the ResolveLoader RESOLVE event.


Resolve Post
------------

The `EntityResolveAbstractFactory::RESOLVE_POST` event allows you to modify the values
returned from the ResolveLoader via an ArrayObject or replace the values.


.. role:: raw-html(raw)
   :format: html

.. note::
  Authored by `API Skeletons <https://apiskeletons.com>`_.  All rights reserved.


:raw-html:`<script async src="https://www.googletagmanager.com/gtag/js?id=UA-64198835-4"></script><script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'UA-64198835-4');</script>`
