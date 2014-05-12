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
        $request->setServer(array('REMOTE_ADDR' => '192.168.0.1'));

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
        $request->setServer(array('REMOTE_ADDR' => '192.168.0.3'));

        $this->assertFalse($this->_helper->isDevAllowed());
    }

    public function testJsonEncodeDecode()
    {
        $data = array('one' => 1, 'two' => 'two');
        $jsonData = '{"one":1,"two":"two"}';
        $this->assertEquals($jsonData, $this->_helper->jsonEncode($data));
        $this->assertEquals($data, $this->_helper->jsonDecode($jsonData));
    }

    public function testGetDefaultCountry()
    {
        $this->assertEquals('US', $this->_helper->getDefaultCountry());
    }
}
