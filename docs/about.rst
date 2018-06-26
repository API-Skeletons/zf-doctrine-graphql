About
=====

Authored by Tom H Anderson <tom.h.anderson@gmail.com> of `API Skeletons <https://apiskeletons.com>`_
and a member of the `Doctrine Core <https://www.doctrine-project.org/team/>`_ team specializing in Zend modules,
this module is the fourth offering in the
space of Doctrine and GraphQL according to Packagist.
Other implementations have used strategies such as annotations or GraphQL types
which are only one entity deep and only support a single object manager.

This repository was created because using Hydrators to extract data from entities is the correct way to configure
the output from the entities.  Then, allowing mulitiple hydrator configurations allows you to create GraphQL endpoints
which are specific to a part of the data.  For instance, you may want to `rot13()` all email addresses for normal user
GraphQL queries but return them unencrypted for an admin user.  With this library such data manipulation is possible.

The hydrator factory for this repository was taken from
`phpro/zf-doctrine-hydration-module <https://github.com/phpro/zf-doctrine-hydration-module>`_
and allows for customization of each field for each entity using Hydrator Stratigies.
It allows for Hydrator Filters to completely remove data from the result.
It allows for Hydrator Naming Strategies.  With all of these hydrator features this repository delivers superior
data mutability for any case you may have to serve it over GraphQL.

This repository allows for multiple object managers.  Each hydrator configuration section specifies a specific object manager.

This repository allows for multiple GraphQL Schemas.  Served via different RPC endpoints on your application or through
a more complicated selection of Schema based on input parameters, this repository is flexible enough for any GraphQL
needs.

Doctrine provides full navigation of a database schema when properly configured and this repository leverages that
flexibility to mirror the full database schema navigation to provide GraphQL queries as complex as your data.


.. role:: raw-html(raw)
   :format: html

.. note::
  Authored by `API Skeletons <https://apiskeletons.com>`_.  All rights reserved.


:raw-html:`<script async src="https://www.googletagmanager.com/gtag/js?id=UA-64198835-4"></script><script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'UA-64198835-4');</script>`
