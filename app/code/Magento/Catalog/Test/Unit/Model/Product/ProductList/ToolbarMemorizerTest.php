<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\ProductList;

use Magento\Catalog\Block\Product\ProductList\Toolbar as ToolbarBlock;
use Magento\Catalog\Model\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests sort order validation and session fallback before the toolbar selects an available order.
 */
class ToolbarMemorizerTest extends TestCase
{
    /**
     * @param mixed $requestOrder
     * @param mixed $rememberedOrder
     * @param bool $remember
     * @param mixed $expectedOrder
     * @return void
     */
    #[DataProvider('orderProvider')]
    public function testGetOrder(
        mixed $requestOrder,
        mixed $rememberedOrder,
        bool $remember,
        mixed $expectedOrder
    ): void {
        $memorizer = $this->createMemorizer($requestOrder, $rememberedOrder, $remember);

        self::assertSame($expectedOrder, $memorizer->getOrder());
    }

    /**
     * @return array
     */
    public static function orderProvider(): array
    {
        return [
            'nonempty array restores session' => [['price'], 'price', true, 'price'],
            'empty array restores session' => [[], 'price', true, 'price'],
            'nested array restores session' => [[['price']], 'price', true, 'price'],
            'nonempty array without remembering' => [['price'], 'price', false, null],
            'empty array without remembering' => [[], 'price', false, null],
            'nested array without remembering' => [[['price']], 'price', false, null],
            'array without remembered order' => [['price'], null, true, null],
            'array with invalid remembered order' => [['price'], ['name'], true, false],
            'string' => ['price', 'name', true, 'price'],
            'empty string' => ['', 'price', true, ''],
            'true' => [true, 'price', true, true],
            'false' => [false, 'price', true, false],
            'absent without remembering' => [null, 'price', false, null],
            'absent with remembered order' => [null, 'price', true, 'price'],
            'absent without remembered order' => [null, null, true, null],
            'invalid remembered order' => [null, ['price'], true, false],
            'integer preserved' => [1, 'price', true, 1],
        ];
    }

    /**
     * @param mixed $requestOrder
     * @param mixed $rememberedOrder
     * @param bool $remember
     * @param string $expectedOrder
     * @return void
     */
    #[DataProvider('currentOrderProvider')]
    public function testGetCurrentOrder(
        mixed $requestOrder,
        mixed $rememberedOrder,
        bool $remember,
        string $expectedOrder
    ): void {
        $memorizer = $this->createMemorizer($requestOrder, $rememberedOrder, $remember);
        $toolbar = (new ObjectManager($this))->getObject(
            ToolbarBlock::class,
            [
                'toolbarMemorizer' => $memorizer,
                '_availableOrder' => ['name' => 'Name', 'price' => 'Price'],
                '_orderField' => 'name',
            ]
        );

        self::assertSame($expectedOrder, $toolbar->getCurrentOrder());
    }

    /**
     * @return array
     */
    public static function currentOrderProvider(): array
    {
        return [
            'nonempty array restores session' => [['price'], 'price', true, 'price'],
            'empty array restores session' => [[], 'price', true, 'price'],
            'nested array restores session' => [[['price']], 'price', true, 'price'],
            'nonempty array uses default' => [['price'], 'price', false, 'name'],
            'empty array uses default' => [[], 'price', false, 'name'],
            'nested array uses default' => [[['price']], 'price', false, 'name'],
            'array without session uses default' => [['price'], null, true, 'name'],
            'array with invalid session uses default' => [['price'], ['name'], true, 'name'],
            'missing order restores session' => [null, 'price', true, 'price'],
            'remembering disabled uses default' => [null, 'price', false, 'name'],
            'invalid session uses default' => [null, ['price'], true, 'name'],
            'valid order overrides session' => ['price', 'name', true, 'price'],
            'unknown order uses default' => ['unknown', 'price', true, 'name'],
        ];
    }

    /**
     * @return void
     */
    public function testMemorizeParamsDoesNotStoreArrayOrder(): void
    {
        $request = $this->createMock(Http::class);
        $values = [Toolbar::ORDER_PARAM_NAME => ['price']];
        $request->method('getParam')->willReturnCallback(static fn ($key) => $values[$key] ?? null);
        $session = $this->createMock(Session::class);
        $calls = [];
        $session->method('__call')->willReturnCallback(static function (string $method) use (&$calls): bool {
            $calls[] = $method;
            return false;
        });
        $config = $this->createMock(ScopeConfigInterface::class);
        $config->method('isSetFlag')->willReturn(true);
        $memorizer = new ToolbarMemorizer(new Toolbar($request), $session, $config);

        $memorizer->memorizeParams();

        self::assertContains('getParamsMemorizeDisabled', $calls);
        self::assertNotContains('setData', $calls);
        self::assertNull($memorizer->getOrder());
    }

    /**
     * @param mixed $requestOrder
     * @param mixed $rememberedOrder
     * @param bool $remember
     * @return ToolbarMemorizer
     */
    private function createMemorizer(
        mixed $requestOrder,
        mixed $rememberedOrder,
        bool $remember
    ): ToolbarMemorizer {
        $request = $this->createMock(Http::class);
        $request->expects(self::once())
            ->method('getParam')
            ->with(Toolbar::ORDER_PARAM_NAME)
            ->willReturn($requestOrder);
        $session = $this->createMock(Session::class);
        $readsSession = ($requestOrder === null || is_array($requestOrder)) && $remember;
        $session->expects($readsSession ? self::once() : self::never())
            ->method('getData')
            ->with(Toolbar::ORDER_PARAM_NAME)
            ->willReturn($rememberedOrder);
        $config = $this->createMock(ScopeConfigInterface::class);
        $config->method('isSetFlag')
            ->with(ToolbarMemorizer::XML_PATH_CATALOG_REMEMBER_PAGINATION)
            ->willReturn($remember);

        return new ToolbarMemorizer(new Toolbar($request), $session, $config);
    }
}
