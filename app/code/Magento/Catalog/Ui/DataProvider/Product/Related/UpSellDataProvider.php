<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Related;

/**
 * Class UpSellDataProvider
 */
class UpSellDataProvider extends AbstractDataProvider
{
    /**
     * {@inheritdoc}
     */
    protected function getLinkType()
    {
        return 'up_sell';
    }
}
