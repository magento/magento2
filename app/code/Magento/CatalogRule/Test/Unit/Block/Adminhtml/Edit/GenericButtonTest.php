<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\CatalogRule\Block\Adminhtml\Edit\GenericButton;
use Magento\CatalogRule\Controller\RegistryConstants;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
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

        $this->model = new GenericButton(
            $contextMock,
            $this->registryMock
        );
    }

    public function testCanRender()
    {
        $name = "Catalog Rule";
        $this->assertEquals($name, $this->model->canRender($name));
    }

    public function testGetUrl()
    {
        $url = "http://magento.com/catalogRule/";
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
            ->with(RegistryConstants::CURRENT_CATALOG_RULE_ID)
            ->willReturn($ruleMock);

        $this->assertEquals($ruleId, $this->model->getRuleId());
    }

    public function testGetRuleIdWithoutRule()
    {
        $this->assertNull($this->model->getRuleId());
    }
}
