<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Shipping\Helper;

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

    public function setUp()
    {
        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->helper = $objectManagerHelper->getObject(
            'Magento\Shipping\Helper\Carrier',
            array(
                'context' => $this->getMock('Magento\Framework\App\Helper\Context', array(), array(), '', false),
                'locale' => $this->getMock('Magento\Framework\LocaleInterface'),
                'scopeConfig' => $this->scopeConfig
            )
        );
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
        return array(
            array(array(), array('carrier1' => array())),
            array(array(), array('carrier1' => array('is_online' => 0))),
            array(
                array('carrier1'),
                array('carrier1' => array('is_online' => 1), 'carrier2' => array('is_online' => 0))
            )
        );
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
}
