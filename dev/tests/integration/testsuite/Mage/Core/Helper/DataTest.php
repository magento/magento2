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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    const DATE_TIMEZONE = 'America/Los_Angeles'; // hardcoded in the installation

    const DATE_FORMAT_SHORT_ISO = 'M/d/yy'; // en_US
    const DATE_FORMAT_SHORT = 'n/j/y';

    const TIME_FORMAT_SHORT_ISO = 'h:mm a'; // en_US
    const TIME_FORMAT_SHORT = 'g:i A'; // // but maybe "a"

    const DATETIME_FORMAT_SHORT_ISO = 'M/d/yy h:mm a';
    const DATETIME_FORMAT_SHORT = 'n/j/y g:i A';

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_helper = null;

    /**
     * @var DateTime
     */
    protected $_dateTime = null;

    public function setUp()
    {
        $this->_helper = new Mage_Core_Helper_Data;
        $this->_dateTime = new DateTime;
        $this->_dateTime->setTimezone(new DateTimeZone(self::DATE_TIMEZONE));
    }

    public function testGetEncryptor()
    {
        $this->assertInstanceOf('Mage_Core_Model_Encryption', $this->_helper->getEncryptor());
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

    public function testFormatDate()
    {
        $this->assertEquals($this->_dateTime->format(self::DATE_FORMAT_SHORT), $this->_helper->formatDate());

        $this->assertEquals(
            $this->_dateTime->format(self::DATETIME_FORMAT_SHORT), $this->_helper->formatDate(null, 'short', true)
        );

        $zendDate = new Zend_Date($this->_dateTime->format('U'));
        $this->assertEquals(
            $zendDate->toString(self::DATETIME_FORMAT_SHORT_ISO),
            $this->_helper->formatTime($zendDate, 'short', true)
        );
    }

    public function testFormatTime()
    {
        $this->assertEquals($this->_dateTime->format(self::TIME_FORMAT_SHORT), $this->_helper->formatTime());

        $this->assertEquals(
            $this->_dateTime->format(self::DATETIME_FORMAT_SHORT), $this->_helper->formatTime(null, 'short', true)
        );

        $zendDate = new Zend_Date($this->_dateTime->format('U'));
        $this->assertEquals(
            $zendDate->toString(self::TIME_FORMAT_SHORT_ISO),
            $this->_helper->formatTime($zendDate, 'short')
        );
    }

    public function testEncryptDecrypt()
    {
        $initial = md5(uniqid());
        $encrypted = $this->_helper->encrypt($initial);
        $this->assertNotEquals($initial, $encrypted);
        $this->assertEquals($initial, $this->_helper->decrypt($encrypted));
    }

    public function testValidateKey()
    {
        $validKey = md5(uniqid());
        $this->assertInstanceOf('Magento_Crypt', $this->_helper->validateKey($validKey));
    }

    public function testGetRandomString()
    {
        $string = $this->_helper->getRandomString(10);
        $this->assertEquals(10, strlen($string));
    }

    public function testGetValidateHash()
    {
        $password = uniqid();
        $hash = $this->_helper->getHash($password);

        $this->assertTrue(is_string($hash));
        $this->assertTrue($this->_helper->validateHash($password, $hash));
    }

    public function testGetStoreId()
    {
        $this->assertTrue(is_numeric($this->_helper->getStoreId()));
    }

    public function testRemoveAccents()
    {
        $noReplacementsNeeded = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $this->assertEquals($noReplacementsNeeded, $this->_helper->removeAccents($noReplacementsNeeded));
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
        $_SERVER['REMOTE_ADDR'] = '192.168.0.1';
        $this->assertTrue($this->_helper->isDevAllowed());
    }

    /**
     * @magentoConfigFixture current_store dev/restrict/allow_ips 192.168.0.1
     * @magentoAppIsolation enabled
     */
    public function testIsDevAllowedFalse()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.0.3';
        $this->assertFalse($this->_helper->isDevAllowed());
    }

    public function testGetCacheTypes()
    {
        $this->assertTrue(is_array($this->_helper->getCacheTypes()));
        $this->assertTrue(is_array($this->_helper->getCacheBetaTypes()));
    }

    public function testCopyFieldset()
    {
        $fieldset = 'sales_copy_order';
        $aspect = 'to_edit';
        $data = array(
            'customer_email' => 'admin@example.com',
            'customer_group_id' => '1',
        );
        $source = new Varien_Object($data);
        $target = new Varien_Object();
        $expectedTarget = new Varien_Object($data);
        $expectedTarget->setDataChanges(true); // hack for assertion

        $this->assertFalse($this->_helper->copyFieldset($fieldset, $aspect, 'invalid_source', array()));
        $this->assertFalse($this->_helper->copyFieldset('invalid_fieldset', $aspect, array(), array()));
        $this->assertTrue($this->_helper->copyFieldset($fieldset, $aspect, $source, $target));
        $this->assertEquals($expectedTarget, $target);
    }

    public function testDecorateArray()
    {
        $original = array(
            array('value' => 1),
            array('value' => 2),
            array('value' => 3),
        );
        $decorated = array(
            array('value' => 1, 'is_first' => true, 'is_odd' => true),
            array('value' => 2, 'is_even' => true),
            array('value' => 3, 'is_last' => true, 'is_odd' => true),
        );

        // arrays
        $this->assertEquals($decorated, $this->_helper->decorateArray($original, ''));

        // Varien_Object
        $sample = array(
            new Varien_Object($original[0]),
            new Varien_Object($original[1]),
            new Varien_Object($original[2]),
        );
        $decoratedVo = array(
            new Varien_Object($decorated[0]),
            new Varien_Object($decorated[1]),
            new Varien_Object($decorated[2]),
        );
        foreach ($decoratedVo as $obj) {
            $obj->setDataChanges(true); // hack for assertion
        }
        $this->assertEquals($decoratedVo, $this->_helper->decorateArray($sample, ''));
    }

    public function testAssocToXml()
    {
        $data = array(
            'one' => 1,
            'two' => array(
                'three' => 3,
                'four' => '4',
            ),
        );
        $result = $this->_helper->assocToXml($data);
        $expectedResult = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<_><one>1</one><two><three>3</three><four>4</four></two></_>

XML;
        $this->assertInstanceOf('SimpleXMLElement', $result);
        $this->assertEquals($expectedResult, $result->asXML());
    }

    /**
     * @param array $array
     * @param string $rootName
     * @expectedException Magento_Exception
     * @dataProvider assocToXmlExceptionDataProvider
     */
    public function testAssocToXmlException($array, $rootName = '_')
    {
        $this->_helper->assocToXml($array, $rootName);
    }

    public function assocToXmlExceptionDataProvider()
    {
        return array(
            array(array(), ''),
            array(array(), 0),
            array(array(1, 2, 3)),
            array(array('root' => 1), 'root'),
        );
    }

    public function testXmlToAssoc()
    {
        $xmlstr = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<_><one>1</one><two><three>3</three><four>4</four></two></_>
XML;
        $result = $this->_helper->xmlToAssoc(new SimpleXMLElement($xmlstr));
        $this->assertEquals(array('one' => '1', 'two' => array('three' => '3', 'four'  => '4')), $result);
    }

    public function testJsonEncodeDecode()
    {
        $data = array('one' => 1, 'two' => 'two');
        $jsonData = '{"one":1,"two":"two"}';
        $this->assertEquals($jsonData, $this->_helper->jsonEncode($data));
        $this->assertEquals($data, $this->_helper->jsonDecode($jsonData));
    }

    public function testUniqHash()
    {
        $hashOne = $this->_helper->uniqHash();
        $hashTwo = $this->_helper->uniqHash();
        $this->assertTrue(is_string($hashOne));
        $this->assertTrue(is_string($hashTwo));
        $this->assertNotEquals($hashOne, $hashTwo);
    }

    public function testGetDefaultCountry()
    {
        $this->assertEquals('US', $this->_helper->getDefaultCountry());
    }
}
