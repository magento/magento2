``reverse``
===========

.. versionadded:: 1.6
    Support for strings has been added in Twig 1.6.

The ``reverse`` filter reverses a sequence, a mapping, or a string:

.. code-block:: jinja

    {% for use in users|reverse %}
        ...
    {% endfor %}

    {{ '1234'|reverse }}

    {# outputs 4321 #}

.. note::

    It also works with objects implementing the `Traversable`_ interface.

.. _`Traversable`: http://php.net/Traversable
