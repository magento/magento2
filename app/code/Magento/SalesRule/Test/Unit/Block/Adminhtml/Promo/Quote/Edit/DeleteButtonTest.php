<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Block\Adminhtml\Promo\Quote\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\DeleteButton;
use Magento\SalesRule\Model\RegistryConstants;
use Magento\SalesRule\Model\Rule;
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
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->escaperMock = $this->createMock(Escaper::class);

        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);
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
        $deleteUrl = 'http://magento.com/admin/promo_quote/delete/id/42';
        $ruleMock = $this->createMock(Rule::class);
        $ruleMock->expects($this->once())
            ->method('getId')
            ->willReturn($ruleId);
        
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(RegistryConstants::CURRENT_SALES_RULE)
            ->willReturn($ruleMock);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/delete', ['id' => $ruleId])
            ->willReturn($deleteUrl);

        // Test the double escaping chain
        $this->escaperMock->expects($this->once())
            ->method('escapeHtml')
            ->with('Are you sure you want to delete this?')
            ->willReturn('Are you sure you want to delete this?');

        $this->escaperMock->expects($this->once())
            ->method('escapeJs')
            ->with('Are you sure you want to delete this?')
            ->willReturn('Are you sure you want to delete this?');

        $result = $this->model->getButtonData();

        $this->assertIsArray($result);
        $this->assertEquals('Delete', $result['label']->render());
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
        $contextMockForTest = $this->createMock(Context::class);
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
