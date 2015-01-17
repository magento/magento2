<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Helper;

use Magento\TestFramework\Helper\ObjectManager;

class UrlRewriteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\UrlRewrite\Helper\UrlRewrite
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = (new ObjectManager($this))->getObject('Magento\UrlRewrite\Helper\UrlRewrite');
    }

    /**
     * @dataProvider requestPathDataProvider
     */
    public function testValidateRequestPath($requestPath)
    {
        $this->assertTrue($this->_helper->validateRequestPath($requestPath));
    }

    /**
     * @dataProvider requestPathExceptionDataProvider
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testValidateRequestPathException($requestPath)
    {
        $this->_helper->validateRequestPath($requestPath);
    }

    /**
     * @dataProvider requestPathDataProvider
     */
    public function testValidateSuffix($suffix)
    {
        $this->assertTrue($this->_helper->validateSuffix($suffix));
    }

    /**
     * @dataProvider requestPathExceptionDataProvider
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testValidateSuffixException($suffix)
    {
        $this->_helper->validateSuffix($suffix);
    }

    public function requestPathDataProvider()
    {
        return [
            'no leading slash' => ['correct/request/path'],
            'leading slash' => ['another/good/request/path/']
        ];
    }

    public function requestPathExceptionDataProvider()
    {
        return [
            'two slashes' => ['request/path/with/two//slashes'],
            'three slashes' => ['request/path/with/three///slashes'],
            'anchor' => ['request/path/with#anchor']
        ];
    }
}
