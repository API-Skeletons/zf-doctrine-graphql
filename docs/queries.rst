Running Queries
===============

This section is intended for the developer who needs to write queries against an implementation of this repository.

Queries are not special to this repository.  The format of queries are exactly what GraphQL is spec'd out to be.
For each implementation of GraphQL the filtering of data is not defined.  In order to build the filters for this
an underscore approach is used.  `fieldName_filter` is the format for all filters.

An example query:

Fetch at most 100 performances in CA for each artist with 'Dead' in their name.

.. code-block:: php

    $query = "{ artist ( filter: { name_contains: \"Dead\" } ) { name performance ( filter: { _limit: 100 state:\"CA\" } ) { performanceDate venue } } }";


Filters
-------

For each field, which is not a reference to another entity, a colletion of filters exist.
Given an entity which contains a `name` field you may directly filter the name using

.. code-block:: js

    filter: { name: "Grateful Dead" }


You may only use each field's filter once per filter().  Should a child record have the same name as a parent
it will share the filter names but fitlers are specific to the entity they filter upon.

Provided Filters
.. note::

    fieldName_eq         -  Equals; same as name: value.  DateTime not supported.
    fieldName_neq        -  Not Equals
    fieldName_gt         -  Greater Than
    fieldName_lt         -  Less Than
    fieldName_gte        -  Greater Than or Equal To
    fieldName_lte        -  Less Than or Equal To
    fieldName_in         -  Filter for values in an array
    fieldName_notin      -  Filter for values not in an array
    fieldName_between    -  Fiilter between `from` and `to` values.  Good substitute for DateTime Equals.
    fieldName_contains   -  Strings only. Similar to a Like query as `like '%value%'`
    fieldName_startswith -  Strings only. A like query from the beginning of the value `like 'value%'`
    fieldName_endswith   -  Strings only. A like query from the end of the value `like '%value'`
    fieldName_memberof  -   Matches a value in an array field.
    fieldName_isnull     -  Takes a boolean.  If TRUE return results where the field is null.
                              If FALSE returns results where the field is not null.
                              NOTE: acts as "isEmpty" for collection filters.  A value of false will
                              be handled as though it were null.
    fieldName_sort       -  Sort the result by this field.  Value is 'asc' or 'desc'
    fieldName_distinct   -  Return a unique list of fieldName.  Only one distinct fieldName allowed per filter.


The format for using these filters is:

.. code-block:: js

    filter: { name_endswith: "Dead" }


For isnull the paramter is a boolean

.. code-block:: js

    filter: { name_isnull: false  }


For in and notin an array of values is expected

.. code-block:: js

    filter: { name_in: ["Phish", "Legion of Mary"] }


For the between filter two parameters are necessary.  This is very useful for date ranges and number queries.

.. code-block:: js

    filter: { year_between: { from: 1966 to: 1995 } }


To select a distinct list of years

.. code-block:: js

    { artist ( filter: { id:2 } ) { performance( filter: { year_distinct: true year_sort: "asc" } ) { year } } }


All filters are AND filters.  For OR support use multiple aliases queries and aggregate them.
TODO:  Add `orx` and `andx` support


Pagination
----------

The filter supports `_skip` and `_limit`.  There is a configuration
variable to set the max limit size and anything under this limit is
valid.  To select a page of data set the `_skip:10 _limit:10` and
increment `_skip` by the `_limit` for each request.  These pagination
filters exist for filtering collections too.
