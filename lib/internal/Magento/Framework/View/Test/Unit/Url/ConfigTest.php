<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Url;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Url\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
        )->disableOriginalConstructor()
            ->getMock();
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
        )->willReturn(
            $expectedValue
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
