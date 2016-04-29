<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Config;

use Magento\Payment\Gateway\Config\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigTest
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var Config */
    protected $model;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();
    }

    public function testGetValue()
    {
        $field = 'field';
        $storeId = 1;
        $methodCode = 'code';
        $pathPattern = 'pattern/%s/%s';
        $expected = 'expected value';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                sprintf($pathPattern, $methodCode, $field),
                ScopeInterface::SCOPE_STORE,
                $storeId
            )->willReturn($expected);

        $this->model = new Config($this->scopeConfigMock, $methodCode, $pathPattern);
        $this->assertEquals($expected, $this->model->getValue($field, $storeId));
    }

    public function testGetValueWithDefaultPathPattern()
    {
        $field = 'field';
        $storeId = 1;
        $methodCode = 'code';
        $expected = 'expected value';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                sprintf(Config::DEFAULT_PATH_PATTERN, $methodCode, $field),
                ScopeInterface::SCOPE_STORE,
                $storeId
            )->willReturn($expected);

        $this->model = new Config($this->scopeConfigMock, $methodCode);
        $this->assertEquals($expected, $this->model->getValue($field, $storeId));
    }
}
