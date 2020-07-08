<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Block\Adminhtml\Promo\Quote\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\GenericButton;
use Magento\SalesRule\Model\RegistryConstants;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenericButtonTest extends TestCase
{
    /**
     * @var GenericButton
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var MockObject
     */
    protected $registryMock;

    protected function setUp(): void
    {
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->registryMock = $this->createMock(Registry::class);
        $contextMock = $this->createMock(Context::class);

        $contextMock->expects($this->once())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->model = (new ObjectManager($this))->getObject(
            GenericButton::class,
            [
                'context' => $contextMock,
                'registry' => $this->registryMock
            ]
        );
    }

    public function testCanRender()
    {
        $name = "Catalog Rule";
        $this->assertEquals($name, $this->model->canRender($name));
    }

    public function testGetUrl()
    {
        $url = "http://magento.com/salesRule/";
        $route = 'button';
        $params = ['unit' => 'test'];

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with($route, $params)
            ->willReturn($url);

        $this->assertEquals($url, $this->model->getUrl($route, $params));
    }

    public function testGetRuleId()
    {
        $ruleId = 42;
        $ruleMock = new DataObject(['id' => $ruleId]);
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(RegistryConstants::CURRENT_SALES_RULE)
            ->willReturn($ruleMock);

        $this->assertEquals($ruleId, $this->model->getRuleId());
    }

    public function testGetRuleIdWithoutRule()
    {
        $this->assertNull($this->model->getRuleId());
    }
}
