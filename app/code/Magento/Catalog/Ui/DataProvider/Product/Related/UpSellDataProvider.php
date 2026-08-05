<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Related;

/**
 * Class UpSellDataProvider
 *
 * @api
 * @since 101.0.0
 */
class UpSellDataProvider extends AbstractDataProvider
{
    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    protected function getLinkType()
    {
        return 'up_sell';
    }
}
