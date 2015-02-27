<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $_helper = null;

    protected function setUp()
    {
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/fancy_uri';
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Url\Helper\Data'
        );
    }

    public function testGetCurrentBase64Url()
    {
        $this->assertEquals('aHR0cDovL2xvY2FsaG9zdA,,', $this->_helper->getCurrentBase64Url());
    }

    public function testGetEncodedUrl()
    {
        $this->assertEquals('aHR0cDovL2xvY2FsaG9zdA,,', $this->_helper->getEncodedUrl());
        $this->assertEquals('aHR0cDovL2V4YW1wbGUuY29tLw,,', $this->_helper->getEncodedUrl('http://example.com/'));
    }
}
