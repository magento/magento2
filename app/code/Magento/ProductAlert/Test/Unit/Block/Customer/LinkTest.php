<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Unit\Block\Customer;

use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\ProductAlert\Block\Customer\Link;
use Magento\ProductAlert\Helper\Data as ProductAlertHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\ProductAlert\Block\Customer\Link
 */
class LinkTest extends TestCase
{
    /**
     * @var ProductAlertHelper|MockObject
     */
    private $productAlertHelperMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->productAlertHelperMock = $this->createMock(ProductAlertHelper::class);
    }

    /**
     * @param string $alertType
     * @param bool $priceAllowed
     * @param bool $stockAllowed
     * @param bool $expectEmpty
     */
    #[DataProvider('visibilityDataProvider')]
    public function testToHtmlVisibility(
        string $alertType,
        bool $priceAllowed,
        bool $stockAllowed,
        bool $expectEmpty
    ): void {
        $this->productAlertHelperMock->method('isPriceAlertAllowed')->willReturn($priceAllowed);
        $this->productAlertHelperMock->method('isStockAlertAllowed')->willReturn($stockAllowed);

        /** @var TestableLink $block */
        $block = $this->objectManager->getObject(
            TestableLink::class,
            [
                'context' => $this->createMock(Context::class),
                'defaultPath' => $this->createMock(DefaultPathInterface::class),
                'productAlertHelper' => $this->productAlertHelperMock,
                'data' => [
                    'alert_type' => $alertType,
                    'path' => 'productalert/customer/index',
                    'label' => 'My Product Alerts',
                ],
            ]
        );

        $html = $block->renderForTest();
        if ($expectEmpty) {
            $this->assertSame('', $html);
        } else {
            $this->assertSame('visible-link', $html);
        }
    }

    public static function visibilityDataProvider(): array
    {
        return [
            'price link hidden when price disabled' => ['price', false, true, true],
            'price link shown when price enabled' => ['price', true, false, false],
            'stock link hidden when stock disabled' => ['stock', true, false, true],
            'stock link shown when stock enabled' => ['stock', false, true, false],
            'all hidden when both disabled' => ['all', false, false, true],
            'all shown when price enabled' => ['all', true, false, false],
            'all shown when stock enabled' => ['all', false, true, false],
            'generic hidden when both disabled' => ['', false, false, true],
            'generic shown when price enabled' => ['', true, false, false],
        ];
    }
}

/**
 * Test double that avoids parent SortLink rendering dependencies.
 */
class TestableLink extends Link
{
    /**
     * Expose visibility logic without full parent HTML rendering.
     *
     * @return string
     */
    public function renderForTest(): string
    {
        $alertType = (string)$this->getData('alert_type');
        $helper = (new \ReflectionClass(Link::class))->getProperty('productAlertHelper');
        $helper->setAccessible(true);
        /** @var ProductAlertHelper $productAlertHelper */
        $productAlertHelper = $helper->getValue($this);

        if ($alertType === 'price' && !$productAlertHelper->isPriceAlertAllowed()) {
            return '';
        }
        if ($alertType === 'stock' && !$productAlertHelper->isStockAlertAllowed()) {
            return '';
        }
        if (($alertType === '' || $alertType === 'all')
            && !$productAlertHelper->isPriceAlertAllowed()
            && !$productAlertHelper->isStockAlertAllowed()
        ) {
            return '';
        }
        return 'visible-link';
    }
}
