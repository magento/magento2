<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Related;

/**
 * Class RelatedDataProvider
 *
 * @api
 * @since 2.1.0
 */
class RelatedDataProvider extends AbstractDataProvider
{
    /**
     * {@inheritdoc
     * @since 2.1.0
     */
    protected function getLinkType()
    {
        return 'relation';
    }
}
