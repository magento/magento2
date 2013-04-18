``format``
==========

The ``format`` filter formats a given string by replacing the placeholders
(placeholders follows the `printf`_ notation):

.. code-block:: jinja

    {{ "I like %s and %s."|format(foo, "bar") }}

    {# returns I like foo and bar
       if the foo parameter equals to the foo string. #}

.. _`printf`: http://www.php.net/printf

.. seealso:: :doc:`replace<replace>`
