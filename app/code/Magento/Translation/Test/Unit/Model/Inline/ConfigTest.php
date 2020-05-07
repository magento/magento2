<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Test\Unit\Model\Inline;

use Magento\Developer\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Translation\Model\Inline\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var Data|MockObject
     */
    protected $helperMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->helperMock = $this->createPartialMock(Data::class, ['isDevAllowed']);
        $this->model = new Config(
            $this->scopeConfigMock,
            $this->helperMock
        );
    }

    public function testIsActive()
    {
        $store = 'some store';
        $result = 'result';
        $scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $scopeConfig->expects(
            $this->once()
        )->method(
            'isSetFlag'
        )->with(
            'dev/translate_inline/active',
            ScopeInterface::SCOPE_STORE,
            $store
        )->willReturn(
            $result
        );
        $objectManager = new ObjectManager($this);
        $config = $objectManager->getObject(
            Config::class,
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
        )->willReturn(
            $result
        );

        $this->assertEquals($result, $this->model->isDevAllowed($store));
    }
}
