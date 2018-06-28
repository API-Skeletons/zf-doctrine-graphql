Documenting Your Entities
=========================

Introspection of entities is a core component to GraphQL.  The introspection allows you to
document your types.  Because entities are types there is a section inside each
hydrator configuration for documenting your entity and fields through introspection.


.. code-block:: php

    'documentation' => [
        '_entity' => 'The Artist entity contains bands, groups, and individual performers.',
        'performance' => 'A collection of Performances by the Artist',
        'artistGroup' => 'Artist Groups this Artist belongs to.',
        'name' => 'The name of the performer.',
        'icon' => 'The Artist icon',
        'createdAt' => 'DateTime the Artist record was created',
        'abbreviation' => 'An abbreviation for the Artist',
        'description' => 'A description of the Artist',
        'id' => 'Primary Key for the Artist',
    ],

There is one special field, `_entity` which is the description for the entity itself.
The rest of the fields describe documentation for each field.

Documentation is specific to each hydrator section allowing you to describe the same entity
in different ways.  The Documentation will be returned in tools like `GraphiQL <https://github.com/graphql/graphiql>`_

GraphiQL is the standard for browsing introspected GraphQL types.  zf-doctrine-graphql fully supports
GraphiQL.



.. role:: raw-html(raw)
   :format: html

.. note::
  Authored by `API Skeletons <https://apiskeletons.com>`_.  All rights reserved.


:raw-html:`<script async src="https://www.googletagmanager.com/gtag/js?id=UA-64198835-4"></script><script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'UA-64198835-4');</script>`
