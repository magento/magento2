<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\CatalogRule\Block\Adminhtml\Edit\DeleteButton;
use Magento\CatalogRule\Controller\RegistryConstants;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteButtonTest extends TestCase
{
    /**
     * @var DeleteButton
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

    /**
     * @var MockObject
     */
    protected $contextMock;

    /**
     * @var MockObject
     */
    protected $escaperMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->escaperMock = $this->createMock(Escaper::class);

        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);
        // Add escaper mock in case the fallback mechanism uses context->getEscaper()
        $this->contextMock->expects($this->any())->method('getEscaper')->willReturn($this->escaperMock);

        $this->model = new DeleteButton(
            $this->contextMock,
            $this->registryMock
        );
    }

    /**
     * Test empty response without a present rule.
     */
    public function testGetButtonDataWithoutRule()
    {
        $this->assertEquals([], $this->model->getButtonData());
    }

    /**
     * Test button data with rule present and proper escaping
     */
    public function testGetButtonDataWithRule()
    {
        $ruleId = 42;
        $deleteUrl = 'http://magento.com/admin/catalog_rule/delete/id/42';
        $ruleMock = new DataObject(['id' => $ruleId]);
        
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(RegistryConstants::CURRENT_CATALOG_RULE_ID)
            ->willReturn($ruleMock);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/delete', ['id' => $ruleId])
            ->willReturn($deleteUrl);

        // Test the double escaping chain
        $this->escaperMock->expects($this->once())
            ->method('escapeHtml')
            ->with('Are you sure you want to do this?')
            ->willReturn('Are you sure you want to do this?');

        $this->escaperMock->expects($this->once())
            ->method('escapeJs')
            ->with('Are you sure you want to do this?')
            ->willReturn('Are you sure you want to do this?');

        $result = $this->model->getButtonData();

        $this->assertIsArray($result);
        $this->assertEquals('Delete Rule', $result['label']->getText());
        $this->assertEquals('delete', $result['class']);
        $this->assertStringContainsString('deleteConfirm', $result['on_click']);
        $this->assertStringContainsString($deleteUrl, $result['on_click']);
        $this->assertEquals(20, $result['sort_order']);
    }

    /**
     * Test constructor uses context escaper.
     */
    public function testConstructorUsesContextEscaper()
    {
        // Create a separate context mock to test escaper usage
        $contextMockForTest = $this->createMock(\Magento\Backend\Block\Widget\Context::class);
        $contextMockForTest->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);
        $contextMockForTest->expects($this->once())->method('getEscaper')->willReturn($this->escaperMock);
        
        // Test that the constructor calls context->getEscaper()
        $deleteButton = new DeleteButton(
            $contextMockForTest,
            $this->registryMock
        );
        
        // Verify the button was created successfully
        $this->assertInstanceOf(DeleteButton::class, $deleteButton);
    }
}
