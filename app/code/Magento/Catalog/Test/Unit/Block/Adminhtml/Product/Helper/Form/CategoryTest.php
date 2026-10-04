<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Helper\Form;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->authorization = $this->createMock(AuthorizationInterface::class);
        $this->objectManager = new ObjectManager($this);
        $objects = [
            [
                SecureHtmlRenderer::class,
                $this->createMock(SecureHtmlRenderer::class)
            ],
            [
                Random::class,
                $this->createMock(Random::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);
    }

    /**
     * @param $isAllowed
     */
    #[DataProvider('isAllowedDataProvider')]
    public function testIsAllowed($isAllowed)
    {
        $this->authorization->method('isAllowed')->willReturn($isAllowed);
        $model = $this->objectManager->getObject(
            Category::class,
            ['authorization' => $this->authorization]
        );
        switch ($isAllowed) {
            case true:
                $this->assertEquals('select', $model->getType());
                $this->assertNull($model->getClass());
                break;
            case false:
                $this->assertEquals('hidden', $model->getType());
                $this->assertStringContainsString('hidden', $model->getClass());
                break;
        }
    }

    /**
     * @return array
     */
    public static function isAllowedDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    public function testGetAfterElementHtml()
    {
        $model = $this->objectManager->getObject(
            Category::class,
            ['authorization' => $this->authorization]
        );
        $this->authorization->method('isAllowed')->willReturn(false);
        $this->assertEmpty($model->getAfterElementHtml());
    }
}
