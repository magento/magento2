<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Model\Inline;

use \Magento\Translation\Model\Inline\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Developer\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->helperMock = $this->getMock(\Magento\Developer\Helper\Data::class, ['isDevAllowed'], [], '', false);
        $this->model = new Config(
            $this->scopeConfigMock,
            $this->helperMock
        );
    }

    public function testIsActive()
    {
        $store = 'some store';
        $result = 'result';
        $scopeConfig = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $scopeConfig->expects(
            $this->once()
        )->method(
            'isSetFlag'
        )->with(
            $this->equalTo('dev/translate_inline/active'),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->equalTo($store)
        )->will(
            $this->returnValue($result)
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $config = $objectManager->getObject(
            \Magento\Translation\Model\Inline\Config::class,
            ['scopeConfig' => $scopeConfig]
        );
        $this->assertEquals($result, $config->isActive($store));
    }

    public function testIsDevAllowed()
    {
        $store = 'some store';
        $result = 'result';

        $this->helperMock->expects(
            $this->once()
        )->method(
            'isDevAllowed'
        )->with(
            $store
        )->will(
            $this->returnValue($result)
        );

        $this->assertEquals($result, $this->model->isDevAllowed($store));
    }
}
