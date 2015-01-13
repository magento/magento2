<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Fixture;

use Magento\CatalogRule\Test\Repository\CatalogPriceRule as Repository;
use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;

/**
 * Class CatalogPriceRule
 *
 */
class CatalogPriceRule extends DataFixture
{
    /**
     * {@inheritdoc}
     */
    protected function _initData()
    {
        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoCatalogRuleCatalogPriceRule($this->_dataConfig, $this->_data);

        //Default data set
        $this->switchData(Repository::CATALOG_PRICE_RULE);
    }

    /**
     * Get the rule name value
     */
    public function getRuleName()
    {
        return $this->getData('fields/name/value');
    }

    /**
     * Get the discount amount value
     */
    public function getDiscountAmount()
    {
        return $this->getData('fields/discount_amount/value');
    }
}
