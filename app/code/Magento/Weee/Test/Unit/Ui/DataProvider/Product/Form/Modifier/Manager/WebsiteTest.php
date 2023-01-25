<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Ui\DataProvider\Product\Form\Modifier\Manager;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Weee\Ui\DataProvider\Product\Form\Modifier\Manager\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WebsiteTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Website
     */
    protected $model;

    /**
     * @var LocatorInterface|MockObject
     */
    protected $locatorMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var DirectoryHelper|MockObject
     */
    protected $directoryHelperMock;

    /**
     * @var EavAttribute|MockObject
     */
    protected $eavAttributeMock;

    /**
     * @var ProductInterface|MockObject
     */
    protected $productMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->locatorMock = $this->getMockBuilder(LocatorInterface::class)
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->setMethods(['hasSingleStore'])
            ->getMockForAbstractClass();
        $this->directoryHelperMock = $this->getMockBuilder(DirectoryHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavAttributeMock = $this->getMockBuilder(EavAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->getMockForAbstractClass();

        $this->model = $this->objectManager->getObject(Website::class, [
            'locator' => $this->locatorMock,
            'storeManager' => $this->storeManagerMock,
            'directoryHelper' => $this->directoryHelperMock,
        ]);
    }

    public function testGetWebsites()
    {
        $this->directoryHelperMock->expects($this->once())
            ->method('getBaseCurrencyCode')
            ->willReturn('USD');
        $this->storeManagerMock->expects($this->once())
            ->method('hasSingleStore')
            ->willReturn(true);

        $this->assertSame(
            [
                [
                    'value' => 0,
                    'label' => 'All Websites USD',
                ]
            ],
            $this->model->getWebsites($this->productMock, $this->eavAttributeMock)
        );
    }

    public function testIsMultiWebsites()
    {
        $this->storeManagerMock->expects($this->once())
            ->method('hasSingleStore')
            ->willReturn(true);

        $this->assertFalse($this->model->isMultiWebsites());
    }
}
