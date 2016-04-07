<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Ui\DataProvider\Product\Form\Modifier\Manager;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Weee\Ui\DataProvider\Product\Form\Modifier\Manager\Website;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;

/**
 * Class WebsiteTest
 */
class WebsiteTest extends \PHPUnit_Framework_TestCase
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
     * @var LocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $locatorMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var DirectoryHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryHelperMock;

    /**
     * @var EavAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavAttributeMock;

    /**
     * @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    protected function setUp()
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

        $this->assertSame(false, $this->model->isMultiWebsites());
    }
}
