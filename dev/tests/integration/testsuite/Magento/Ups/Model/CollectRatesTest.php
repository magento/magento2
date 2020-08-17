<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ups\Model;

/**
 * Integration tests for online shipping carriers.
 * @magentoAppIsolation enabled
 */
class CollectRatesTest extends \Magento\Shipping\Model\CollectRatesAbstract
{
    /**
     * @var string
     */
    protected $carrier = 'ups';

    /**
     * @var string
     */
    protected $errorMessage = 'This shipping method is currently unavailable. ' .
    'If you would like to ship using this shipping method, please contact us.';

    /**
     * @magentoConfigFixture default_store carriers/ups/active 1
     * @magentoConfigFixture default_store carriers/ups/type UPS
     * @magentoConfigFixture default_store carriers/ups/sallowspecific 1
     * @magentoConfigFixture default_store carriers/ups/specificcountry UK
     * @magentoConfigFixture default_store carriers/ups/showmethod 1
     */
    // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod
    public function testCollectRatesWhenShippingCarrierIsAvailableAndNotApplicable()
    {
        parent::testCollectRatesWhenShippingCarrierIsAvailableAndNotApplicable();
    }

    /**
     * @magentoConfigFixture default_store carriers/ups/active 0
     * @magentoConfigFixture default_store carriers/ups/type UPS
     * @magentoConfigFixture default_store carriers/ups/sallowspecific 1
     * @magentoConfigFixture default_store carriers/ups/specificcountry UK
     * @magentoConfigFixture default_store carriers/ups/showmethod 1
     */
    // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod
    public function testCollectRatesWhenShippingCarrierIsNotAvailableAndNotApplicable()
    {
        parent::testCollectRatesWhenShippingCarrierIsNotAvailableAndNotApplicable();
    }
}
