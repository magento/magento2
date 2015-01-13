<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture;

class UpsellProducts extends AssignProducts
{
    protected $assignType = 'up_sell';

    protected $group = 'upsells';
}
