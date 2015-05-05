<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Fixture\OrderInjectable;

use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\DataSource;

/**
 * Coupon code data.
 */
class CouponCode extends DataSource
{
    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $data
     * @param array $params [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $data, array $params = [])
    {
        $this->params = $params;
        if (isset($data['value']) && $data['value'] instanceof SalesRule) {
            $this->data = $data['value'];
            return;
        }
        if (isset($data['dataSet'])) {
            $salesRule = $fixtureFactory->createByCode('salesRule', ['dataSet' => $data['dataSet']]);
            $salesRule->persist();
            $this->data = $salesRule;
        }
    }
}
