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
use Magento\Framework\Escaper;

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

    /**
     * @var Escaper|MockObject
     */
    private $escaper;

    protected function setUp(): void
    {
        $context = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getScopeConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfig);
        $this->escaper = $this->createPartialMock(Escaper::class, ['escapeQuote']);
        $this->unit = (new ObjectManager($this))->getObject(
            Header::class,
            [
               'context' => $context,
               'escaper' => $this->escaper
            ]
        );
    }

    public function testGetWelcomeDefault()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with('design/header/welcome', ScopeInterface::SCOPE_STORE)
            ->willReturn("Message d'accueil par défaut");

        $this->escaper->expects($this->once())
            ->method('escapeQuote')
            ->with("Message d'accueil par défaut", true)
            ->willReturn("Message d\'accueil par défaut");

        $this->assertEquals("Message d\'accueil par défaut", $this->unit->getWelcome());
    }
}
