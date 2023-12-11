<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Helper\UrlRewrite;
use PHPUnit\Framework\TestCase;

class UrlRewriteTest extends TestCase
{
    /**
     * @var UrlRewrite
     */
    protected $_helper;

    protected function setUp(): void
    {
        $this->_helper = (new ObjectManager($this))->getObject(
            UrlRewrite::class
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
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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
