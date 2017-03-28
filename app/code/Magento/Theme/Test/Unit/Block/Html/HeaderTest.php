<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Block\Html;

class HeaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Block\Html\Header
     */
    protected $unit;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    protected function setUp()
    {
        $context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->setMethods(['getScopeConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()->getMock();
        $context->expects($this->once())->method('getScopeConfig')->will($this->returnValue($this->scopeConfig));

        $this->unit = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            \Magento\Theme\Block\Html\Header::class,
            ['context' => $context]
        );
    }

    public function testGetWelcomeDefault()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with('design/header/welcome', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn('Welcome Message');

        $this->assertEquals('Welcome Message', $this->unit->getWelcome());
    }
}
