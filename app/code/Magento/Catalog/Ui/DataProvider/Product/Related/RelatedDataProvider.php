<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Related;

/**
 * Class RelatedDataProvider
 *
 * @api
 * @since 101.0.0
 */
class RelatedDataProvider extends AbstractDataProvider
{
    /**
     * {@inheritdoc
     * @since 101.0.0
     */
    protected function getLinkType()
    {
        return 'relation';
    }
}
