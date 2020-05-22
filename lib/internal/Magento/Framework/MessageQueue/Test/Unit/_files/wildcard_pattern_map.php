<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
