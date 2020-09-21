<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Html;

use Magento\Framework\App\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Block\Html\Header;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HeaderTest extends TestCase
{
    /**
     * @var Header
     */
    protected $unit;

    /**
     * @var MockObject
     */
    protected $scopeConfig;

    protected function setUp(): void
    {
        $context = $this->getMockBuilder(Context::class)
            ->setMethods(['getScopeConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(Config::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfig);

        $this->unit = (new ObjectManager($this))->getObject(
            Header::class,
            ['context' => $context]
        );
    }

    public function testGetWelcomeDefault()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with('design/header/welcome', ScopeInterface::SCOPE_STORE)
            ->willReturn('Welcome Message');

        $this->assertEquals('Welcome Message', $this->unit->getWelcome());
    }
}
