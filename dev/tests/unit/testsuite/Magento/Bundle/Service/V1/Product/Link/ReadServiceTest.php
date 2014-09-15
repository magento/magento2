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
namespace Magento\Bundle\Service\V1\Product\Link;


use Magento\TestFramework\Helper\ObjectManager;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Service\V1\Product\Link\ReadService
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\ProductRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\Bundle\Service\V1\Data\Product\LinkConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $linkConverter;

    /**
     * @var \Magento\Bundle\Model\Product\Type\Interceptor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productType;

    /**
     * @var \Magento\Bundle\Model\Resource\Option\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionCollection;

    /**
     * @var \Magento\Bundle\Model\Resource\Selection\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectionCollection;

    /**
     * @var \Magento\Bundle\Model\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    private $option;

    /**
     * @var \Magento\Bundle\Service\V1\Data\Product\Link|\PHPUnit_Framework_MockObject_MockObject
     */
    private $link;

    private $storeId = 2;

    private $optionIds = [1, 2, 3];

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->productRepository = $this->getMockBuilder('Magento\Catalog\Model\ProductRepository')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->productType = $this->getMockBuilder('Magento\Bundle\Model\Product\Type\Interceptor')
            ->setMethods(['getOptionsCollection', 'setStoreFilter', 'getSelectionsCollection', 'getOptionsIds'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->option = $this->getMockBuilder('Magento\Bundle\Model\Option')
            ->setMethods(['getSelections', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionCollection = $this->getMockBuilder('Magento\Bundle\Model\Resource\Option\Collection')
            ->setMethods(['appendSelections'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectionCollection = $this->getMockBuilder('Magento\Bundle\Model\Resource\Selection\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getTypeInstance', 'getStoreId', 'getTypeId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->link = $this->getMockBuilder('Magento\Bundle\Service\V1\Data\Product\Link')
            ->disableOriginalConstructor()
            ->getMock();

        $this->linkConverter = $this->getMockBuilder(
            'Magento\Bundle\Service\V1\Data\Product\LinkConverter'
        )
            ->setMethods(['createDataFromModel'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $helper->getObject(
            'Magento\Bundle\Service\V1\Product\Link\ReadService',
            [
                'productRepository' => $this->productRepository,
                'linkConverter' => $this->linkConverter
            ]
        );
    }

    public function testGetChildren()
    {
        $productSku = 'productSku';

        $this->getOptions();

        $this->productRepository->expects($this->any())->method('get')->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')->will($this->returnValue('bundle'));

        $this->productType->expects($this->once())->method('setStoreFilter')->with(
            $this->equalTo($this->storeId),
            $this->product
        );
        $this->productType->expects($this->once())->method('getSelectionsCollection')
            ->with($this->equalTo($this->optionIds), $this->equalTo($this->product))
            ->will($this->returnValue($this->selectionCollection));
        $this->productType->expects($this->once())->method('getOptionsIds')->with($this->equalTo($this->product))
            ->will($this->returnValue($this->optionIds));

        $this->optionCollection->expects($this->once())->method('appendSelections')
            ->with($this->equalTo($this->selectionCollection))
            ->will($this->returnValue([$this->option]));

        $this->option->expects($this->any())->method('getSelections')->will($this->returnValue([$this->product]));

        $this->linkConverter->expects($this->once())->method('createDataFromModel')
            ->with($this->equalTo($this->product), $this->equalTo($this->product))
            ->will($this->returnValue($this->link));

        $this->assertEquals([$this->link], $this->model->getChildren($productSku));
    }

    /**
     * @expectedException \Magento\Webapi\Exception
     * @expectedExceptionCode 403
     */
    public function testGetChildrenException()
    {
        $productSku = 'productSku';

        $this->productRepository->expects($this->once())->method('get')->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')->will($this->returnValue('simple'));

        $this->assertEquals([$this->link], $this->model->getChildren($productSku));
    }

    private function getOptions()
    {
        $this->product->expects($this->once())->method('getStoreId')->will($this->returnValue($this->storeId));
        $this->product->expects($this->any())->method('getTypeInstance')->will($this->returnValue($this->productType));

        $this->productType->expects($this->once())->method('setStoreFilter')
            ->with($this->equalTo($this->storeId), $this->equalTo($this->product));
        $this->productType->expects($this->once())->method('getOptionsCollection')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue($this->optionCollection));
    }
}
