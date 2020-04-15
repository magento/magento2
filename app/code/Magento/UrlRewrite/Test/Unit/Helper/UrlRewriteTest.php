<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class UrlRewriteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\UrlRewrite\Helper\UrlRewrite
     */
    protected $_helper;

    protected function setUp(): void
    {
        $this->_helper = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            \Magento\UrlRewrite\Helper\UrlRewrite::class
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
     */
    public function testValidateRequestPathException($requestPath)
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

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
     */
    public function testValidateSuffixException($suffix)
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

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
