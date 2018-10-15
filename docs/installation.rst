Installation
============

Installation of this module uses composer. For composer documentation, please refer to
`getcomposer.org <http://getcomposer.org/>`_ ::

    $ composer require api-skeletons/zf-doctrine-graphql

Once installed, add **ZF\\Doctrine\\GraphQL** to your list of modules inside
`config/application.config.php` or `config/modules.config.php`.

zf-component-installer
----------------------

If you use `zf-component-installer <https://github.com/zendframework/zf-component-installer>`_,
that plugin will install zf-doctrine-graphql as a module for you.


zf-doctrine-criteria configuration
----------------------------------

You must copy the config for zf-doctrine-criteria to your autoload directory::

    $ cp vendor/api-skeletons/zf-doctrine-criteria/config/zf-doctrine-criteria.global.php.dist config/autoload/zf-doctrine-criteria.global.php

.. role:: raw-html(raw)
   :format: html

.. note::
  Authored by `API Skeletons <https://apiskeletons.com>`_.  All rights reserved.


:raw-html:`<script async src="https://www.googletagmanager.com/gtag/js?id=UA-64198835-4"></script><script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'UA-64198835-4');</script>`
