<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Helper\Form;

class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->authorization = $this->getMockBuilder(\Magento\Framework\AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
            \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category::class,
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
            \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category::class,
            ['authorization' => $this->authorization]
        );
        $this->authorization->expects($this->any())
            ->method('isAllowed')
            ->willReturn(false);
        $this->assertEmpty($model->getAfterElementHtml());
    }
}
