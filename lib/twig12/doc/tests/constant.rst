``constant``
============

``constant`` checks if a variable has the exact same value as a constant. You
can use either global constants or class constants:

.. code-block:: jinja

    {% if post.status is constant('Post::PUBLISHED') %}
        the status attribute is exactly the same as Post::PUBLISHED
    {% endif %}
