<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Helper\Form;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
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
        $this->authorization = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @dataProvider isAllowedDataProvider
     * @param $isAllowed
     */
    public function testIsAllowed($isAllowed)
    {
        $this->authorization->expects($this->any())
            ->method('isAllowed')
            ->willReturn($isAllowed);
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
    public function isAllowedDataProvider()
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
        $this->authorization->expects($this->any())
            ->method('isAllowed')
            ->willReturn(false);
        $this->assertEmpty($model->getAfterElementHtml());
    }
}
