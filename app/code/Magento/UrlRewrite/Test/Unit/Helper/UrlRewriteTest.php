<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class UrlRewriteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\UrlRewrite\Helper\UrlRewrite
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\UrlRewrite\Helper\UrlRewrite'
        );
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testValidateSuffixException($suffix)
    {
        $this->_helper->validateSuffix($suffix);
    }

    /**
     * @return array
     */
    public function requestPathDataProvider()
    {
        return [
            'no leading slash' => ['correct/request/path'],
            'leading slash' => ['another/good/request/path/']
        ];
    }

    /**
     * @return array
     */
    public function requestPathExceptionDataProvider()
    {
        return [
            'two slashes' => ['request/path/with/two//slashes'],
            'three slashes' => ['request/path/with/three///slashes'],
            'anchor' => ['request/path/with#anchor']
        ];
    }
}
