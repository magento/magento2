<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

return [
    ['customer.created', '/^customer\.created$/'],
    ['customer.created.one', '/^customer\.created\.one$/'],
    ['customer.created.one.two', '/^customer\.created\.one\.two$/'],
    ['customer.created.two', '/^customer\.created\.two$/'],
    ['customer.updated', '/^customer\.updated$/'],
    ['cart.created', '/^cart\.created$/'],
    ['customer.deleted', '/^customer\.deleted$/'],
    ['cart.created.one', '/^cart\.created\.one$/'],
    ['customer.*', '/^customer\.[^\.]+$/'],
    ['customer.#', '/^customer\..+$/'],
    ['customer.*.one', '/^customer\.[^\.]+\.one$/'],
    ['*.created.*', '/^[^\.]+\.created\.[^\.]+$/'],
    ['*.created.#', '/^[^\.]+\.created\..+$/'],
    ['#', '/^.+$/']
];
