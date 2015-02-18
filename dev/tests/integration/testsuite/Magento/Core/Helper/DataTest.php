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

    public function testJsonEncodeDecode()
    {
        $data = ['one' => 1, 'two' => 'two'];
        $jsonData = '{"one":1,"two":"two"}';
        $this->assertEquals($jsonData, $this->_helper->jsonEncode($data));
        $this->assertEquals($data, $this->_helper->jsonDecode($jsonData));
    }
}
