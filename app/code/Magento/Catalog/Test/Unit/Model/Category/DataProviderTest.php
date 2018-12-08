<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Category;

use Magento\Catalog\Model\Category\DataProvider;
use Magento\Catalog\Model\Category\FileInfo;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EavValidationRules|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavValidationRules;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryCollectionFactory;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var CategoryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryFactory;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var Type|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavEntityMock;

    /**
     * @var FileInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileInfo;

    /**
     * @var PoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $modifierPool;

    protected function setUp()
    {
        $this->eavValidationRules = $this->getMockBuilder(EavValidationRules::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection->expects($this->any())
            ->method('addAttributeToSelect')
            ->with('*')
            ->willReturnSelf();

        $this->categoryCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->categoryCollectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->collection);

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavEntityMock = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->categoryFactory = $this->getMockBuilder(CategoryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileInfo = $this->getMockBuilder(FileInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->modifierPool = $this->getMockBuilder(PoolInterface::class)->getMockForAbstractClass();
    }

    /**
     * @return DataProvider
     */
    private function getModel()
    {
        $this->eavEntityMock->expects($this->any())
            ->method('getAttributeCollection')
            ->willReturn([]);

        $this->eavConfig->expects($this->any())
            ->method('getEntityType')
            ->with('catalog_category')
            ->willReturn($this->eavEntityMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        /** @var DataProvider $model */
        $model = $objectManager->getObject(
            DataProvider::class,
            [
                'eavValidationRules' => $this->eavValidationRules,
                'categoryCollectionFactory' => $this->categoryCollectionFactory,
                'storeManager' => $this->storeManager,
                'registry' => $this->registry,
                'eavConfig' => $this->eavConfig,
                'request' => $this->request,
                'categoryFactory' => $this->categoryFactory,
                'pool' => $this->modifierPool
            ]
        );

        $objectManager->setBackwardCompatibleProperty(
            $model,
            'fileInfo',
            $this->fileInfo
        );

        return $model;
    }

    public function testGetDataNoCategory()
    {
        $this->registry->expects($this->once())
            ->method('registry')
            ->with('category')
            ->willReturn(null);

        $model = $this->getModel();
        $this->assertNull($model->getData());
    }

    public function testGetDataNoFileExists()
    {
        $fileName = 'filename.ext1';
        $categoryId = 1;

        $categoryData = [
            'image' => $fileName,
        ];

        $imageBackendMock = $this->getMockBuilder(\Magento\Catalog\Model\Category\Attribute\Backend\Image::class)
            ->disableOriginalConstructor()
            ->getMock();

        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getBackend')
            ->willReturn($imageBackendMock);

        $categoryMock = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnMap([
                ['', null, $categoryData],
                ['image', null, $categoryData['image']],
            ]);
        $categoryMock->expects($this->any())
            ->method('getExistsStoreValueFlag')
            ->with('url_key')
            ->willReturn(false);
        $categoryMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $categoryMock->expects($this->once())
            ->method('getId')
            ->willReturn($categoryId);
        $categoryMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn(['image' => $attributeMock]);

        $this->registry->expects($this->once())
            ->method('registry')
            ->with('category')
            ->willReturn($categoryMock);

        $this->fileInfo->expects($this->once())
            ->method('isExist')
            ->with($fileName)
            ->willReturn(false);

        $model = $this->getModel();
        $result = $model->getData();

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey($categoryId, $result);
        $this->assertArrayNotHasKey('image', $result[$categoryId]);
    }

    public function testGetData()
    {
        $fileName = 'filename.png';
        $mime = 'image/png';
        $size = 1;

        $categoryId = 1;
        $categoryUrl = 'category_url';

        $categoryData = [
            'image' => $fileName,
        ];

        $expects = [
            [
                'name' => $fileName,
                'url' => $categoryUrl,
                'size' => $size,
                'type' => $mime,
            ],
        ];

        $imageBackendMock = $this->getMockBuilder(\Magento\Catalog\Model\Category\Attribute\Backend\Image::class)
            ->disableOriginalConstructor()
            ->getMock();

        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getBackend')
            ->willReturn($imageBackendMock);

        $categoryMock = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnMap([
                ['', null, $categoryData],
                ['image', null, $categoryData['image']],
            ]);
        $categoryMock->expects($this->any())
            ->method('getExistsStoreValueFlag')
            ->with('url_key')
            ->willReturn(false);
        $categoryMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $categoryMock->expects($this->once())
            ->method('getId')
            ->willReturn($categoryId);
        $categoryMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn(['image' => $attributeMock]);
        $categoryMock->expects($this->once())
            ->method('getImageUrl')
            ->willReturn($categoryUrl);

        $this->registry->expects($this->once())
            ->method('registry')
            ->with('category')
            ->willReturn($categoryMock);

        $this->fileInfo->expects($this->once())
            ->method('isExist')
            ->with($fileName)
            ->willReturn(true);
        $this->fileInfo->expects($this->once())
            ->method('getStat')
            ->with($fileName)
            ->willReturn(['size' => $size]);
        $this->fileInfo->expects($this->once())
            ->method('getMimeType')
            ->with($fileName)
            ->willReturn($mime);

        $model = $this->getModel();
        $result = $model->getData();

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey($categoryId, $result);
        $this->assertArrayHasKey('image', $result[$categoryId]);

        $this->assertEquals($expects, $result[$categoryId]['image']);
    }

    public function testGetMetaWithoutParentInheritanceResolving()
    {
        $categoryMock = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry->expects($this->once())
            ->method('registry')
            ->with('category')
            ->willReturn($categoryMock);
        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn(['image' => $attributeMock]);
        $categoryMock->expects($this->never())
            ->method('getParentId');

        $this->modifierPool->expects($this->once())
            ->method('getModifiersInstances')
            ->willReturn([]);

        $this->getModel()->getMeta();
    }
}
