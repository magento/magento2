<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    const DATE_TIMEZONE = 'America/Los_Angeles';

    // hardcoded in the installation

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_helper = null;

    /**
     * @var \DateTime
     */
    protected $_dateTime = null;

    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Helper\Data');
        $this->_dateTime = new \DateTime();
        $this->_dateTime->setTimezone(new \DateTimeZone(self::DATE_TIMEZONE));
    }

    public function testCurrency()
    {
        $price = 10.00;
        $priceHtml = '<span class="price">$10.00</span>';
        $this->assertEquals($priceHtml, $this->_helper->currency($price));
        $this->assertEquals($priceHtml, $this->_helper->formatCurrency($price));
    }

    public function testFormatPrice()
    {
        $price = 10.00;
        $priceHtml = '<span class="price">$10.00</span>';
        $this->assertEquals($priceHtml, $this->_helper->formatPrice($price));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testIsDevAllowedDefault()
    {
        $this->assertTrue($this->_helper->isDevAllowed());
    }

    /**
     * @magentoConfigFixture current_store dev/restrict/allow_ips 192.168.0.1
     * @magentoAppIsolation enabled
     */
    public function testIsDevAllowedTrue()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\TestFramework\Request $request */
        $request = $objectManager->get('Magento\TestFramework\Request');
        $request->setServer(['REMOTE_ADDR' => '192.168.0.1']);

        $this->assertTrue($this->_helper->isDevAllowed());
    }

    /**
     * @magentoConfigFixture current_store dev/restrict/allow_ips 192.168.0.1
     * @magentoAppIsolation enabled
     */
    public function testIsDevAllowedFalse()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\TestFramework\Request $request */
        $request = $objectManager->get('Magento\TestFramework\Request');
        $request->setServer(['REMOTE_ADDR' => '192.168.0.3']);

        $this->assertFalse($this->_helper->isDevAllowed());
    }

    public function testJsonEncodeDecode()
    {
        $data = ['one' => 1, 'two' => 'two'];
        $jsonData = '{"one":1,"two":"two"}';
        $this->assertEquals($jsonData, $this->_helper->jsonEncode($data));
        $this->assertEquals($data, $this->_helper->jsonDecode($jsonData));
    }

    public function testGetDefaultCountry()
    {
        $this->assertEquals('US', $this->_helper->getDefaultCountry());
    }
}
