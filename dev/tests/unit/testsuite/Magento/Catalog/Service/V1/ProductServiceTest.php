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

namespace Magento\Catalog\Service\V1;

use Magento\Framework\Service\V1\Data\SearchCriteria;
use Magento\Framework\Service\V1\Data\SearchCriteriaBuilder;

/**
 * Test for \Magento\Catalog\Service\V1\ProductService
 */
class ProductServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\catalog\Service\V1\Product\ProductLoader
     */
    protected $_productLoaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Catalog\Model\Product
     */
    protected $_productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $productCollection;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Catalog\Service\V1\Data\Product\SearchResultsBuilder
     */
    protected $searchResultsBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Catalog\Service\V1\ProductMetadataService
     */
    protected $metadataServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Catalog\Model\Converter
     */
    protected $converterMock;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchBuilder;

    public function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productFactoryMock = $this->getMockBuilder('Magento\Catalog\Model\ProductFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $productFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_productMock));

        $this->_productLoaderMock = $this->_objectManager
            ->getObject(
                'Magento\Catalog\Service\V1\Product\ProductLoader',
                ['productFactory' => $productFactoryMock]
            );

        $this->productCollection = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->searchResultsBuilderMock = $this->getMockBuilder(
            'Magento\Catalog\Service\V1\Data\Product\SearchResultsBuilder'
        )->disableOriginalConstructor()
            ->getMock();

        $this->metadataServiceMock = $this->getMockBuilder(
            '\Magento\Catalog\Service\V1\Product\MetadataService'
        )->disableOriginalConstructor()
            ->getMock();

        $this->converterMock = $this->getMockBuilder('\Magento\Catalog\Service\V1\Data\Converter')
            ->disableOriginalConstructor()
            ->getMock();

        $filterGroupBuilder = $this->_objectManager
            ->getObject('Magento\Framework\Service\V1\Data\Search\FilterGroupBuilder');
        /** @var SearchCriteriaBuilder $searchBuilder */
        $this->_searchBuilder = $this->_objectManager->getObject(
            'Magento\Framework\Service\V1\Data\SearchCriteriaBuilder',
            ['filterGroupBuilder' => $filterGroupBuilder]
        );
    }

    public function testDelete()
    {
        $productId = 100;
        $productSku = 'sku-001';

        $this->_productMock->expects($this->at(0))->method('getIdBySku')->will($this->returnValue($productId));
        $this->_productMock->expects($this->at(1))->method('load')->with($productId);

        $productData = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $this->converterMock->expects($this->once())
            ->method('createProductDataFromModel')
            ->with($this->_productMock)
            ->will($this->returnValue($productData));

        $productService = $this->_objectManager->getObject(
            'Magento\Catalog\Service\V1\ProductService',
            [
                'productLoader' => $this->_productLoaderMock,
                'converter' => $this->converterMock,
            ]
        );

        $this->assertTrue($productService->delete($productSku));
    }

    public function testDeleteNoSuchEntityException()
    {
        $productId = 0;
        $productSku = 'sku-001';

        $this->_productMock->expects($this->once())->method('getIdBySku')->will($this->returnValue($productId));
        $productService = $this->_createService();

        $this->setExpectedException(
            'Magento\Framework\Exception\NoSuchEntityException',
            "There is no product with provided SKU"
        );

        $productService->delete($productSku);
    }

    public function testSearch()
    {
        $metadata = array();
        $attributeCodes = ['price', 'id', 'sku'];
        foreach ($attributeCodes as $code) {
            $attributeMetadataMock = $this->getMockBuilder('\Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata')
                ->disableOriginalConstructor()
                ->getMock();
            $attributeMetadataMock->expects($this->once())
                ->method('getAttributeCode')
                ->will($this->returnValue($code));
            $metadata[] = $attributeMetadataMock;
        }
        $this->metadataServiceMock->expects($this->any())
            ->method('getProductAttributesMetadata')
            ->will($this->returnValue($metadata));

        $collection = $this->getMockBuilder('\Magento\Catalog\Model\Resource\Product\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->any())->method('addAttributeToSelect');
        $collection->expects($this->any())->method('joinAttribute');
        $collection->expects($this->any())->method('addOrder')->with(
            $this->equalTo('price'),
            $this->equalTo('ASC')
        );
        $collection->expects($this->once())->method('setCurPage')->with($this->equalTo(1));
        $collection->expects($this->once())->method('setPageSize')->with($this->equalTo(10));
        $collection->expects($this->once())->method('getSize')->will($this->returnValue(5));
        $this->productCollection->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collection));

        $this->_mockReturnValue(
            $collection,
            array(
                'getSize' => 1,
                '_getItems' => array($this->_productMock),
                'getIterator' => new \ArrayIterator(array($this->_productMock))
            )
        );

        $productDataBuilder = $this->getMockBuilder('\Magento\Catalog\Service\V1\Data\ProductBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $productDataBuilder->setPrice('10.000');
        $productDataBuilder->setSku('test');
        $productDataBuilder->setStoreId(10);
        $this->converterMock->expects($this->once())
            ->method('createProductBuilderFromModel')
            ->will($this->returnValue($productDataBuilder));

        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setItems')
            ->with($this->equalTo(array($productDataBuilder->create())));
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue(array($productDataBuilder->create())));

        $productService = $this->_objectManager->getObject(
            'Magento\Catalog\Service\V1\ProductService',
            [
                'productLoader' => $this->_productLoaderMock,
                'productCollection' => $this->productCollection,
                'searchResultsBuilder' => $this->searchResultsBuilderMock,
                'metadataService' => $this->metadataServiceMock,
                'converter' => $this->converterMock,
            ]
        );

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $filterBuilder = $helper->getObject('\Magento\Framework\Service\V1\Data\FilterBuilder');
        $filter = $filterBuilder->setField('price')->setValue('10.000')->setConditionType('eq')->create();
        $this->_searchBuilder->addFilter([$filter]);
        $sortOrderBuilder = $helper->getObject('\Magento\Framework\Service\V1\Data\SortOrderBuilder');
        $sortOrder = $sortOrderBuilder
            ->setField('price')
            ->setDirection(SearchCriteria::SORT_ASC)
            ->create();
        $this->_searchBuilder->addSortOrder($sortOrder);
        $this->_searchBuilder->setCurrentPage(1);
        $this->_searchBuilder->setPageSize(10);
        $productService->search($this->_searchBuilder->create());
    }

    public function testGet()
    {
        $productId = 100;
        $productSku = 'sku-001';

        $this->_productMock->expects($this->at(0))->method('getIdBySku')->will($this->returnValue($productId));
        $this->_productMock->expects($this->at(1))->method('load')->with($productId);
        $productDataBuilder = $this->getMockBuilder('\Magento\Catalog\Service\V1\Data\ProductBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->converterMock->expects($this->once())
            ->method('createProductBuilderFromModel')
            ->with($this->_productMock)
            ->will($this->returnValue($productDataBuilder));

        $productService = $this->_objectManager->getObject(
            'Magento\Catalog\Service\V1\ProductService',
            [
                'productLoader' => $this->_productLoaderMock,
                'converter' => $this->converterMock,
            ]
        );
        $productService->get($productSku);
    }

    public function testGetNoSuchEntityException()
    {
        $productId = 0;
        $productSku = 'sku-001';

        $this->_productMock->expects($this->once())->method('getIdBySku')->will($this->returnValue($productId));
        $productService = $this->_createService();

        $this->setExpectedException(
            'Magento\Framework\Exception\NoSuchEntityException',
            "There is no product with provided SKU"
        );

        $productService->get($productSku);
    }

    public function testCreate()
    {
        $initializationHelper = $this
            ->getMockBuilder('Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper')
            ->disableOriginalConstructor()
            ->getMock();

        $productMapper = $this
            ->getMockBuilder('Magento\Catalog\Service\V1\Data\ProductMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeManager = $this
            ->getMockBuilder('Magento\Catalog\Model\Product\TypeTransitionManager')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Catalog\Service\V1\ProductService $productService */
        $productService = $this->_objectManager->getObject(
            'Magento\Catalog\Service\V1\ProductService',
            [
                'initializationHelper' => $initializationHelper,
                'productMapper' => $productMapper,
                'productTypeManager' => $productTypeManager,
                'productLoader' => $this->_productLoaderMock,
            ]
        );

        $productModel = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $product = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productMapper->expects($this->once())->method('toModel')->with($product)
            ->will($this->returnValue($productModel));

        $initializationHelper->expects($this->once())->method('initialize')->with($productModel);

        $productModel->expects($this->any())->method('validate');
        $productModel->expects($this->any())->method('save');

        $productSku = 'sku-001';
        $productModel->expects($this->once())->method('getId')->will($this->returnValue(100));
        $productModel->expects($this->once())->method('getSku')->will($this->returnValue($productSku));

        $this->assertEquals($productSku, $productService->create($product));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Unable to save product
     */
    public function testCreateStateException()
    {
        $initializationHelper = $this
            ->getMockBuilder('Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper')
            ->disableOriginalConstructor()
            ->getMock();

        $productMapper = $this
            ->getMockBuilder('Magento\Catalog\Service\V1\Data\ProductMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeManager = $this
            ->getMockBuilder('Magento\Catalog\Model\Product\TypeTransitionManager')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Catalog\Service\V1\ProductService $productService */
        $productService = $this->_objectManager->getObject(
            'Magento\Catalog\Service\V1\ProductService',
            [
                'initializationHelper' => $initializationHelper,
                'productMapper' => $productMapper,
                'productTypeManager' => $productTypeManager,
                'productLoader' => $this->_productLoaderMock,
            ]
        );

        $productModel = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $product = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productMapper->expects($this->once())->method('toModel')->with($product)
            ->will($this->returnValue($productModel));

        $initializationHelper->expects($this->once())->method('initialize')->with($productModel);

        $productModel->expects($this->once())->method('validate');
        $productModel->expects($this->once())->method('save');

        $productModel->expects($this->once())->method('getId')->will($this->returnValue(0));

        $productService->create($product);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testCreateInputException()
    {
        $initializationHelper = $this
            ->getMockBuilder('Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper')
            ->disableOriginalConstructor()
            ->getMock();

        $productMapper = $this
            ->getMockBuilder('Magento\Catalog\Service\V1\Data\ProductMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeManager = $this
            ->getMockBuilder('Magento\Catalog\Model\Product\TypeTransitionManager')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Catalog\Service\V1\ProductService $productService */
        $productService = $this->_objectManager->getObject(
            'Magento\Catalog\Service\V1\ProductService',
            [
                'initializationHelper' => $initializationHelper,
                'productMapper' => $productMapper,
                'productTypeManager' => $productTypeManager,
                'productLoader' => $this->_productLoaderMock,
            ]
        );

        $productModel = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $product = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productMapper->expects($this->once())->method('toModel')->with($product)
            ->will($this->returnValue($productModel));

        $initializationHelper->expects($this->once())->method('initialize')->with($productModel);

        $productModel->expects($this->once())->method('validate');
        $productModel->expects($this->once())->method('save')->will(
            $this->returnCallback(
                function () {
                    throw new \Magento\Eav\Model\Entity\Attribute\Exception();
                }
            )
        );

        $productService->create($product);
    }

    public function testUpdate()
    {
        $initializationHelper = $this
            ->getMockBuilder('Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper')
            ->disableOriginalConstructor()
            ->getMock();

        $productMapper = $this
            ->getMockBuilder('Magento\Catalog\Service\V1\Data\ProductMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeManager = $this
            ->getMockBuilder('Magento\Catalog\Model\Product\TypeTransitionManager')
            ->disableOriginalConstructor()
            ->getMock();

        $productLoader = $this
            ->getMockBuilder('Magento\Catalog\Service\V1\Product\ProductLoader')
            ->setMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Catalog\Service\V1\ProductService $productService */
        $productService = $this->_objectManager->getObject(
            'Magento\Catalog\Service\V1\ProductService',
            [
                'initializationHelper' => $initializationHelper,
                'productMapper' => $productMapper,
                'productTypeManager' => $productTypeManager,
                'productLoader' => $productLoader,
            ]
        );

        $productModel = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $product = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $productLoader->expects($this->once())->method('load')
            ->will($this->returnValue($productModel));

        $productMapper->expects($this->once())->method('toModel')->with($product, $productModel)
            ->will($this->returnValue($productModel));

        $initializationHelper->expects($this->once())->method('initialize')->with($productModel);
        $productTypeManager->expects($this->once())->method('processProduct')->with($productModel);

        $productModel->expects($this->any())->method('validate');
        $productModel->expects($this->any())->method('save');

        $productSku = 'sku-001';
        $productModel->expects($this->any())->method('getSku')->will($this->returnValue($productSku));

        $this->assertEquals($productSku, $productService->update(5, $product));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testUpdateInputException()
    {
        $initializationHelper = $this
            ->getMockBuilder('Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper')
            ->disableOriginalConstructor()
            ->getMock();

        $productMapper = $this
            ->getMockBuilder('Magento\Catalog\Service\V1\Data\ProductMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeManager = $this
            ->getMockBuilder('Magento\Catalog\Model\Product\TypeTransitionManager')
            ->disableOriginalConstructor()
            ->getMock();

        $productLoader = $this
            ->getMockBuilder('Magento\Catalog\Service\V1\Product\ProductLoader')
            ->setMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Catalog\Service\V1\ProductService $productService */
        $productService = $this->_objectManager->getObject(
            'Magento\Catalog\Service\V1\ProductService',
            [
                'initializationHelper' => $initializationHelper,
                'productMapper' => $productMapper,
                'productTypeManager' => $productTypeManager,
                'productLoader' => $productLoader,
            ]
        );

        $productModel = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $product = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $productLoader->expects($this->once())->method('load')
            ->will($this->returnValue($productModel));

        $productMapper->expects($this->once())->method('toModel')->with($product, $productModel)
            ->will($this->returnValue($productModel));

        $initializationHelper->expects($this->once())->method('initialize')->with($productModel);
        $productTypeManager->expects($this->once())->method('processProduct')->with($productModel);

        $productModel->expects($this->once())->method('validate');
        $productModel->expects($this->once())->method('save')->will(
            $this->returnCallback(
                function () {
                    throw new \Magento\Eav\Model\Entity\Attribute\Exception();
                }
            )
        );

        $productService->update(5, $product);
    }

    /**
     * @return ProductService
     */
    private function _createService()
    {
        $productService = $this->_objectManager->getObject(
            'Magento\Catalog\Service\V1\ProductService',
            [
                'productLoader' => $this->_productLoaderMock
            ]
        );
        return $productService;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $valueMap
     */
    private function _mockReturnValue($mock, $valueMap)
    {
        foreach ($valueMap as $method => $value) {
            $mock->expects($this->any())->method($method)->will($this->returnValue($value));
        }
    }
}
