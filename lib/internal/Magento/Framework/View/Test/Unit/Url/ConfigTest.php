<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Url;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Url\Config;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $_model;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected function setUp(): void
    {
        $this->_scopeConfig = $this->getMockBuilder(
            ScopeConfigInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->_model = new Config($this->_scopeConfig);
    }

    /**
     * @param $path
     * @param $expectedValue
     *
     * @dataProvider getConfigDataProvider
     */
    public function testGetValue($path, $expectedValue)
    {
        $this->_scopeConfig->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            $path
        )->will(
            $this->returnValue($expectedValue)
        );
        $actual = $this->_model->getValue($path);
        $this->assertEquals($expectedValue, $actual);
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            ['some/valid/path1', 'someValue'],
            ['some/valid/path2', 2],
            ['some/valid/path3', false],
            ['some/invalid/path3', null]
        ];
    }
}
