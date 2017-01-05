<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
