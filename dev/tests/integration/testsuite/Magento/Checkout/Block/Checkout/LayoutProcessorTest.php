<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Block\Checkout;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

class LayoutProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests default country for shipping address.
     *
     * @param string $defaultCountryId
     * @param bool $isCountryValueExpected
     * @magentoConfigFixture default_store checkout/options/display_billing_address_on 1
     * @magentoDataFixture Magento/Backend/_files/allowed_countries_fr.php
     * @dataProvider defaultCountryDataProvider
     */
    public function testShippingAddressCountryId(string $defaultCountryId, bool $isCountryValueExpected): void
    {
        /** @var MutableScopeConfigInterface $mutableConfig */
        $mutableConfig = Bootstrap::getObjectManager()->get(MutableScopeConfigInterface::class);
        $mutableConfig->setValue('general/country/default', $defaultCountryId, ScopeInterface::SCOPE_STORE, 'default');

        /** @var $layoutProcessor LayoutProcessor */
        $layoutProcessor = Bootstrap::getObjectManager()->get(LayoutProcessor::class);

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] = [];
        $data = $layoutProcessor->process($jsLayout);

        $countryId = $data["components"]["checkout"]["children"]["steps"]["children"]["shipping-step"]["children"]
        ["shippingAddress"]["children"]["shipping-address-fieldset"]["children"]["country_id"];

        $isCountryValueExists = array_key_exists('value', $countryId);

        $this->assertEquals($isCountryValueExpected, $isCountryValueExists);
        if ($isCountryValueExpected) {
            $this->assertEquals($defaultCountryId, $countryId['value']);
        }
    }

    /**
     * @return array[]
     */
    public function defaultCountryDataProvider(): array
    {
        return [
            'Default country isn\'t in allowed country list' => [
                'defaultCountryId' => 'US',
                'isCountryValueExpected' => false
            ],
            'Default country is in allowed country list' => [
                'defaultCountryId' => 'FR',
                'isCountryValueExpected' => true
            ],
        ];
    }
}
