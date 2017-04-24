<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Helper;

/**
 * Carrier helper test
 */
class CarrierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Shipping Carrier helper
     *
     * @var \Magento\Shipping\Helper\Carrier
     */
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = \Magento\Shipping\Helper\Carrier::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $context = $arguments['context'];
        $this->scopeConfig = $context->getScopeConfig();
        $this->helper = $objectManagerHelper->getObject($className, $arguments);
    }

    /**
     * @param array $result
     * @param array $carriers
     * @dataProvider getOnlineCarrierCodesDataProvider
     */
    public function testGetOnlineCarrierCodes($result, $carriers)
    {
        $this->scopeConfig->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            'carriers',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->will(
            $this->returnValue($carriers)
        );
        $this->assertEquals($result, $this->helper->getOnlineCarrierCodes());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function getOnlineCarrierCodesDataProvider()
    {
        return [
            [[], ['carrier1' => []]],
            [[], ['carrier1' => ['is_online' => 0]]],
            [
                ['carrier1'],
                ['carrier1' => ['is_online' => 1], 'carrier2' => ['is_online' => 0]]
            ]
        ];
    }

    public function testGetCarrierConfigValue()
    {
        $carrierCode = 'carrier1';
        $configPath = 'title';
        $configValue = 'some title';
        $this->scopeConfig->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            sprintf('carriers/%s/%s', $carrierCode, $configPath),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->will(
            $this->returnValue($configValue)
        );
        $this->assertEquals($configValue, $this->helper->getCarrierConfigValue($carrierCode, $configPath));
    }

    public function testIsCountryInEU()
    {
        $this->scopeConfig->expects(
            $this->exactly(2)
        )->method(
            'getValue'
        )->with(
            'general/country/eu_countries',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->will(
            $this->returnValue("GB")
        );

        $this->assertEquals(true, $this->helper->isCountryInEU("GB"));
        $this->assertEquals(false, $this->helper->isCountryInEU("US"));
    }
}
