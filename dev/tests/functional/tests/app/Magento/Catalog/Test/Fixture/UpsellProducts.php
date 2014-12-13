<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Fixture;

class UpsellProducts extends AssignProducts
{
    protected $assignType = 'up_sell';

    protected $group = 'upsells';
}
