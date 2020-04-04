<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Model;

/**
 * Integration tests for offline shipping carriers.
 * @magentoAppIsolation enabled
 */
class CollectRatesTest extends \Magento\Shipping\Model\CollectRatesAbstract
{
    /**
     * @var string
     */
    protected $carrier = 'flatrate';

    /**
     * @var string
     */
    protected $errorMessage = 'This shipping method is not available. To use this shipping method, please contact us.';

    /**
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store carriers/flatrate/sallowspecific 1
     * @magentoConfigFixture default_store carriers/flatrate/specificcountry UK
     * @magentoConfigFixture default_store carriers/flatrate/showmethod 1
     */
    // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod
    public function testCollectRatesWhenShippingCarrierIsAvailableAndNotApplicable()
    {
        parent::testCollectRatesWhenShippingCarrierIsAvailableAndNotApplicable();
    }

    /**
     * @magentoConfigFixture default_store carriers/flatrate/active 0
     * @magentoConfigFixture default_store carriers/flatrate/sallowspecific 1
     * @magentoConfigFixture default_store carriers/flatrate/specificcountry UK
     * @magentoConfigFixture default_store carriers/flatrate/showmethod 1
     */
    // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod
    public function testCollectRatesWhenShippingCarrierIsNotAvailableAndNotApplicable()
    {
        parent::testCollectRatesWhenShippingCarrierIsNotAvailableAndNotApplicable();
    }
}
