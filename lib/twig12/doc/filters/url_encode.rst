``url_encode``
==============

The ``url_encode`` filter URL encodes a given string:

.. code-block:: jinja

    {{ data|url_encode() }}

.. note::

    Internally, Twig uses the PHP `urlencode`_ function.

.. _`urlencode`: http://php.net/urlencode
