GraphQL for Doctrine using Hydrators
====================================

[![Build Status](https://travis-ci.org/API-Skeletons/zf-doctrine-graphql.svg)](https://travis-ci.org/API-Skeletons/zf-doctrine-graphql)
[![Coverage](https://coveralls.io/repos/github/API-Skeletons/zf-doctrine-graphql/badge.svg?branch=master&124)](https://coveralls.io/repos/github/API-Skeletons/zf-doctrine-graphql/badge.svg?branch=master&124)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)
[![Documentation Status](https://readthedocs.org/projects/zf-doctrine-graphql/badge/?version=latest)](http://graphql.apiskeletons.com/en/latest/?badge=latest)
[![License](https://poser.pugx.org/api-skeletons/zf-doctrine-graphql/license)](https://packagist.org/packages/api-skeletons/zf-doctrine-graphql)
[![Gitter](https://badges.gitter.im/api-skeletons/open-source.svg)](https://gitter.im/api-skeletons/open-source)
[![Total Downloads](https://poser.pugx.org/api-skeletons/zf-doctrine-graphql/downloads)](https://packagist.org/packages/api-skeletons/zf-doctrine-graphql)

This library uses Doctrine native traversal of related objects to provide full GraphQL
querying of entities and all related fields and entities.
Entity metadata is introspected and is therefore Doctrine data driver agnostic.
Data is collected with hydrators thereby
allowing full control over each field using hydrator filters, strategies and naming strategies.
Multiple object managers are supported. Multiple hydrator configurations are supported.
Works with [GraphiQL](https://github.com/graphql/graphiql).

[A range of filters](http://graphql.apiskeletons.com/en/latest/queries.html)
are provided to filter collections at any location in the query.

Doctrine provides easy taversal of your database.  Consider the following imaginary query:
```php
$entity->getRelation()->getField1()
                      ->getField2()
                      ->getManyToOne()->getName()
                                      ->getField3()
       ->getOtherRelation()->getField4()
                           ->getField5()
```

And see it realized in GraphQL with fine grained control over each field via hydrators:

```js
{ entity { relation { field1 field2 manyToOne { name field3 } } otherRelation { field4 field5 } } }
```


[Read the Documentation](http://graphql.apiskeletons.com)
==========================================================
