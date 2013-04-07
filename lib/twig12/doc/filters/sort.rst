``sort``
========

The ``sort`` filter sorts an array:

.. code-block:: jinja

    {% for use in users|sort %}
        ...
    {% endfor %}

.. note::

    Internally, Twig uses the PHP `asort`_ function to maintain index
    association.

.. _`asort`: http://php.net/asort
