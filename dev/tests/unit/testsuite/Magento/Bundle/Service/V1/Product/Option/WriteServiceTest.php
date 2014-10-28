<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Service\V1\Product\Option;

use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class WriteServiceTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Service\V1\Product\Option\WriteService
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\ProductRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Bundle\Service\V1\Data\Product\OptionConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionConverter;

    /**
     * @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Bundle\Model\Product\Type|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productType;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\Bundle\Model\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionModel;

    /**
     * @var \Magento\Bundle\Service\V1\Data\Product\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    private $option;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var \Magento\Bundle\Model\Resource\Option\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionCollection;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->productRepository = $this->getMockBuilder('Magento\Catalog\Model\ProductRepository')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->setMethods(['getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManager')
            ->setMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));

        $this->optionConverter = $this->getMockBuilder('Magento\Bundle\Service\V1\Data\Product\OptionConverter')
            ->setMethods(['createModelFromData', 'createDataFromModel', 'getModelFromData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->productType = $this->getMockBuilder('Magento\Bundle\Model\Product\Type')
            ->setMethods(['getOptionsCollection'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->option = $this->getMockBuilder('Magento\Bundle\Service\V1\Data\Product\Option')
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getTypeId', 'getSku'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionModel = $this->getMockBuilder('Magento\Bundle\Model\Option')
            ->setMethods(['__wakeup', 'getId', 'delete', 'setStoreId', 'save'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionCollection = $this->getMockBuilder('Magento\Bundle\Model\Resource\Option\Collection')
            ->setMethods(['setIdFilter', 'getFirstItem'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            'Magento\Bundle\Service\V1\Product\Option\WriteService',
            [
                'productRepository' => $this->productRepository,
                'type' => $this->productType,
                'storeManager' => $this->storeManager,
                'optionConverter' => $this->optionConverter
            ]
        );
    }

    public function testAdd()
    {
        $productSku = 'oneSku';
        $optionId = 33;

        $this->productRepository->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE));

        $storeId = 1;
        $this->store->expects($this->once())->method('getId')->will($this->returnValue($storeId));

        $this->optionModel->expects($this->once())->method('setStoreId')->with($this->equalTo($storeId));
        $this->optionModel->expects($this->once())->method('save');

        $this->optionConverter->expects($this->once())->method('createModelFromData')
            ->with($this->equalTo($this->option), $this->equalTo($this->product))
            ->will($this->returnValue($this->optionModel));
        $this->optionModel->expects($this->once())->method('getId')
            ->will($this->returnValue($optionId));

        $this->assertEquals($optionId, $this->model->add($productSku, $this->option));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testAddCouldNotSaveException()
    {
        $productSku = 'oneSku';

        $this->productRepository->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE));

        $storeId = 1;
        $this->store->expects($this->once())->method('getId')->will($this->returnValue($storeId));

        $this->optionModel->expects($this->once())->method('setStoreId')->with($this->equalTo($storeId));
        $this->optionModel->expects($this->once())->method('save')->will(
            $this->returnCallback(
                function () {
                    throw new \Exception();
                }
            )
        );

        $this->optionConverter->expects($this->once())->method('createModelFromData')
            ->with($this->equalTo($this->option), $this->equalTo($this->product))
            ->will($this->returnValue($this->optionModel));

        $this->assertEquals($this->option, $this->model->add($productSku, $this->option));
    }

    public function testUpdate()
    {
        $productSku = 'oneSku';
        $optionId = 3;

        $this->productRepository->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE));

        $this->productType->expects($this->once())->method('getOptionsCollection')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue($this->optionCollection));

        $this->optionCollection->expects($this->once())->method('setIdFilter')
            ->with($this->equalTo($optionId));
        $this->optionCollection->expects($this->once())->method('getFirstItem')
            ->will($this->returnValue($this->optionModel));

        $storeId = 1;
        $this->store->expects($this->once())->method('getId')->will($this->returnValue($storeId));

        $this->optionModel->expects($this->once())->method('setStoreId')->with($this->equalTo($storeId));
        $this->optionModel->expects($this->once())->method('getId')->will($this->returnValue($optionId));
        $this->optionModel->expects($this->once())->method('save');

        $this->optionConverter->expects($this->once())->method('getModelFromData')
            ->with($this->equalTo($this->option), $this->equalTo($this->optionModel))
            ->will($this->returnValue($this->optionModel));

        $this->assertTrue($this->model->update($productSku, $optionId, $this->option));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testUpdateCouldNotSaveException()
    {
        $productSku = 'oneSku';
        $optionId = 3;

        $this->productRepository->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE));

        $this->productType->expects($this->once())->method('getOptionsCollection')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue($this->optionCollection));

        $this->optionCollection->expects($this->once())->method('setIdFilter')
            ->with($this->equalTo($optionId));
        $this->optionCollection->expects($this->once())->method('getFirstItem')
            ->will($this->returnValue($this->optionModel));

        $storeId = 1;
        $this->store->expects($this->once())->method('getId')->will($this->returnValue($storeId));

        $this->optionModel->expects($this->once())->method('setStoreId')->with($this->equalTo($storeId));
        $this->optionModel->expects($this->once())->method('getId')->will($this->returnValue($optionId));
        $this->optionModel->expects($this->once())->method('save')->will(
            $this->returnCallback(
                function () {
                    throw new \Exception();
                }
            )
        );

        $this->optionConverter->expects($this->once())->method('getModelFromData')
            ->with($this->equalTo($this->option), $this->equalTo($this->optionModel))
            ->will($this->returnValue($this->optionModel));

        $this->assertTrue($this->model->update($productSku, $optionId, $this->option));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testUpdateNoSuchEntityException()
    {
        $productSku = 'oneSku';
        $optionId = 3;

        $this->productRepository->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE));

        $this->productType->expects($this->once())->method('getOptionsCollection')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue($this->optionCollection));

        $this->optionCollection->expects($this->once())->method('setIdFilter')
            ->with($this->equalTo($optionId));
        $this->optionCollection->expects($this->once())->method('getFirstItem')
            ->will($this->returnValue($this->optionModel));

        $this->optionConverter->expects($this->once())->method('getModelFromData')
            ->with($this->equalTo($this->option), $this->equalTo($this->optionModel))
            ->will($this->returnValue($this->optionModel));

        $this->optionModel->expects($this->once())->method('getId');

        $this->model->update($productSku, $optionId, $this->option);
    }

    public function testRemove()
    {
        $productSku = 'oneSku';
        $optionId = 3;

        $this->productRepository->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE));

        $this->productType->expects($this->once())->method('getOptionsCollection')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue($this->optionCollection));

        $this->optionCollection->expects($this->once())->method('setIdFilter')
            ->with($this->equalTo($optionId));
        $this->optionCollection->expects($this->once())->method('getFirstItem')
            ->will($this->returnValue($this->optionModel));

        $this->optionModel->expects($this->once())->method('getId')->will($this->returnValue($optionId));
        $this->optionModel->expects($this->once())->method('delete');

        $this->assertTrue($this->model->remove($productSku, $optionId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRemoveNoSuchEntityException()
    {
        $productSku = 'oneSku';
        $optionId = 3;

        $this->productRepository->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE));

        $this->productType->expects($this->once())->method('getOptionsCollection')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue($this->optionCollection));

        $this->optionCollection->expects($this->once())->method('setIdFilter')
            ->with($this->equalTo($optionId));
        $this->optionCollection->expects($this->once())->method('getFirstItem')
            ->will($this->returnValue($this->optionModel));

        $this->optionModel->expects($this->once())->method('getId');

        $this->model->remove($productSku, $optionId);
    }

    /**
     * @expectedException \Magento\Webapi\Exception
     * @expectedExceptionCode 403
     */
    public function testRemoveWebApiException()
    {
        $productSku = 'oneSku';
        $optionId = 3;

        $this->productRepository->expects($this->once())->method('get')
            ->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE));
        $this->product->expects($this->once())->method('getSku')
            ->will($this->returnValue($productSku));

        $this->model->remove($productSku, $optionId);
    }
}
