<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Image;

use Magento\Framework\App\Area;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\ObjectManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CacheTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\Product\Image\Cache
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Magento\Framework\View\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewConfig;

    /**
     * @var \Magento\Framework\Config\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeCollection;

    /**
     * @var \Magento\Theme\Model\Config\Customization|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeCustomizationConfig;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attribute;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $adapter;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\Data\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaGalleryCollection;

    protected function setUp()
    {
        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewConfig = $this->getMockBuilder(\Magento\Framework\View\ConfigInterface::class)
            ->getMockForAbstractClass();

        $this->config = $this->getMockBuilder(\Magento\Framework\Config\View::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeCollection = $this->getMockBuilder(\Magento\Theme\Model\ResourceModel\Theme\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageHelper = $this->getMockBuilder(\Magento\Catalog\Helper\Image::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeCustomizationConfig = $this->getMockBuilder(\Magento\Theme\Model\Config\Customization::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->select->expects($this->any())->method('from')->will($this->returnValue($this->select));
        $this->select->expects($this->any())->method('where')->will($this->returnValue($this->select));

        $this->adapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->adapter->expects($this->any())->method('fetchRow')->willReturn([1]);

        $this->connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection->expects($this->any())->method('select')->willReturn($this->select);

        $this->resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTable'])
            ->getMockForAbstractClass();
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connection);
        $this->resource->expects($this->any())->method('getTable')->willReturn('test');

        $this->attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResource', 'loadByCode'])
            ->getMock();
        $this->attribute->expects($this->any())->method('getResource')->willReturn($this->resource);
        $this->attribute->expects($this->any())->method('loadByCode')->willReturnSelf();

        $this->mediaGalleryCollection = $this->getMockBuilder(\Magento\Framework\Data\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            \Magento\Catalog\Model\Product\Image\Cache::class,
            [
                'viewConfig' => $this->viewConfig,
                'themeCollection' => $this->themeCollection,
                'themeCustomizationConfig' => $this->themeCustomizationConfig,
                'imageHelper' => $this->imageHelper,
                'config' => $this->eavConfig,
                'attribute' => $this->attribute
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGenerate()
    {
        $imageFile = 'image.jpg';
        $imageItem = $this->objectManager->getObject(
            \Magento\Framework\DataObject::class,
            [
                'data' => ['file' => $imageFile]
            ]
        );
        $this->mediaGalleryCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$imageItem]));

        $this->product->expects($this->any())
            ->method('getMediaGalleryImages')
            ->willReturn($this->mediaGalleryCollection);

        $data = $this->getTestData();
        $this->config->expects($this->once())
            ->method('getMediaEntities')
            ->with('Magento_Catalog')
            ->willReturn($data);

        $themeMock = $this->getMockBuilder(\Magento\Theme\Model\Theme::class)
            ->disableOriginalConstructor()
            ->getMock();
        $themeMock->expects($this->exactly(3))
            ->method('getCode')
            ->willReturn('Magento\theme');

        $this->themeCollection->expects($this->once())
            ->method('loadRegisteredThemes')
            ->willReturn([$themeMock]);

        $this->themeCustomizationConfig->expects($this->once())
            ->method('getStoresByThemes')
            ->willReturn([$themeMock->getThemeId() => [1]]);

        $this->viewConfig->expects($this->once())
            ->method('getViewConfig')
            ->with([
                'area' => Area::AREA_FRONTEND,
                'themeModel' => $themeMock,
            ])
            ->willReturn($this->config);

        $this->imageHelper->expects($this->exactly(3))
            ->method('init')
            ->will($this->returnValueMap([
                [
                    $this->product,
                    'product_image',
                    $this->getImageData('product_image'),
                    $this->imageHelper
                ],
                [
                    $this->product,
                    'product_small_image',
                    $this->getImageData('product_small_image'),
                    $this->imageHelper
                ],
                [
                    $this->product,
                    'product_thumbnail',
                    $this->getImageData('product_thumbnail'),
                    $this->imageHelper
                ],
            ]));
        $this->imageHelper->expects($this->exactly(3))
            ->method('setImageFile')
            ->with($imageFile)
            ->willReturnSelf();

        $this->imageHelper->expects($this->any())
            ->method('keepAspectRatio')
            ->with($data['product_image']['aspect_ratio'])
            ->willReturnSelf();
        $this->imageHelper->expects($this->any())
            ->method('keepFrame')
            ->with($data['product_image']['frame'])
            ->willReturnSelf();
        $this->imageHelper->expects($this->any())
            ->method('keepTransparency')
            ->with($data['product_image']['transparency'])
            ->willReturnSelf();
        $this->imageHelper->expects($this->any())
            ->method('constrainOnly')
            ->with($data['product_image']['constrain'])
            ->willReturnSelf();
        $this->imageHelper->expects($this->any())
            ->method('backgroundColor')
            ->with($data['product_image']['background'])
            ->willReturnSelf();

        $this->imageHelper->expects($this->exactly(3))
            ->method('save')
            ->will($this->returnSelf());

        $this->model->generate($this->product);
    }

    /**
     * @return array
     */
    protected function getTestData()
    {
        return [
            'product_image' => [
                'type' => 'image',
                'width' => 300,
                'height' => 300,
                'aspect_ratio' => true,
                'frame' => true,
                'transparency' => true,
                'constrain' => true,
                'background' => [255, 255, 255],
            ],
            'product_small_image' => [
                'type' => 'small_image',
                'height' => 200,
            ],
            'product_thumbnail' => [
                'type' => 'thumbnail',
                'width' => 100,
            ],
        ];
    }

    /**
     * @param string $imageId
     * @return array
     */
    protected function getImageData($imageId)
    {
        $imageData = $this->getTestData();
        return [
            'id' => $imageId,
            'type' => $imageData[$imageId]['type'],
            'width' => isset($imageData[$imageId]['width']) ? $imageData[$imageId]['width'] : null,
            'height' => isset($imageData[$imageId]['height']) ? $imageData[$imageId]['height'] : null,
        ];
    }
}
