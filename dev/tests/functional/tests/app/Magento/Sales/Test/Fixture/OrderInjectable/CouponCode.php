<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        if (isset($data['dataset'])) {
            $salesRule = $fixtureFactory->createByCode('salesRule', ['dataset' => $data['dataset']]);
            $salesRule->persist();
            $this->data = $salesRule;
        }
    }
}
