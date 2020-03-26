<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Helper\Form\Gallery;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\Product;
use Magento\Framework\Phrase;
use Magento\MediaStorage\Helper\File\Storage\Database;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileSystemMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readMock;

    /**
     * @var Content|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $content;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaConfigMock;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $galleryMock;

    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageHelper;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $databaseMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->fileSystemMock = $this->createPartialMock(
            \Magento\Framework\Filesystem::class,
            ['stat', 'getDirectoryRead']
        );
        $this->readMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $this->galleryMock = $this->createMock(\Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery::class);
        $this->mediaConfigMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Media\Config::class,
            ['getMediaUrl', 'getMediaPath']
        );
        $this->jsonEncoderMock = $this->getMockBuilder(\Magento\Framework\Json\EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->databaseMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->content = $this->objectManager->getObject(
            \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content::class,
            [
                'mediaConfig' => $this->mediaConfigMock,
                'jsonEncoder' => $this->jsonEncoderMock,
                'filesystem' => $this->fileSystemMock,
                'fileStorageDatabase' => $this->databaseMock
            ]
        );
    }

    public function testGetImagesJson()
    {
        $url = [
            ['file_1.jpg', 'url_to_the_image/image_1.jpg'],
            ['file_2.jpg', 'url_to_the_image/image_2.jpg']
        ];
        $mediaPath = [
            ['file_1.jpg', 'catalog/product/image_1.jpg'],
            ['file_2.jpg', 'catalog/product/image_2.jpg']
        ];

        $sizeMap = [
            ['catalog/product/image_1.jpg', ['size' => 399659]],
            ['catalog/product/image_2.jpg', ['size' => 879394]]
        ];

        $imagesResult = [
            [
                'value_id' => '2',
                'file' => 'file_2.jpg',
                'media_type' => 'image',
                'position' => '0',
                'url' => 'url_to_the_image/image_2.jpg',
                'size' => 879394
            ],
            [
                'value_id' => '1',
                'file' => 'file_1.jpg',
                'media_type' => 'image',
                'position' => '1',
                'url' => 'url_to_the_image/image_1.jpg',
                'size' => 399659
            ]
        ];

        $images = [
            'images' => [
                [
                    'value_id' => '1',
                    'file' => 'file_1.jpg',
                    'media_type' => 'image',
                    'position' => '1'
                ] ,
                [
                    'value_id' => '2',
                    'file' => 'file_2.jpg',
                    'media_type' => 'image',
                    'position' => '0'
                ]
            ]
        ];

        $this->content->setElement($this->galleryMock);
        $this->galleryMock->expects($this->once())->method('getImages')->willReturn($images);
        $this->fileSystemMock->expects($this->once())->method('getDirectoryRead')->willReturn($this->readMock);

        $this->mediaConfigMock->expects($this->any())->method('getMediaUrl')->willReturnMap($url);
        $this->mediaConfigMock->expects($this->any())->method('getMediaPath')->willReturnMap($mediaPath);
        $this->readMock->expects($this->any())->method('stat')->willReturnMap($sizeMap);
        $this->jsonEncoderMock->expects($this->once())->method('encode')->willReturnCallback('json_encode');

        $this->readMock->expects($this->any())
            ->method('isFile')
            ->will($this->returnValue(true));
        $this->databaseMock->expects($this->any())
            ->method('checkDbUsage')
            ->will($this->returnValue(false));

        $this->assertSame(json_encode($imagesResult), $this->content->getImagesJson());
    }

    public function testGetImagesJsonWithoutImages()
    {
        $this->content->setElement($this->galleryMock);
        $this->galleryMock->expects($this->once())->method('getImages')->willReturn(null);

        $this->assertSame('[]', $this->content->getImagesJson());
    }

    public function testGetImagesJsonWithException()
    {
        $this->imageHelper = $this->getMockBuilder(\Magento\Catalog\Helper\Image::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultPlaceholderUrl'])
            ->getMock();

        $this->objectManager->setBackwardCompatibleProperty(
            $this->content,
            'imageHelper',
            $this->imageHelper
        );

        $placeholderUrl = 'url_to_the_placeholder/placeholder.jpg';

        $imagesResult = [
            [
                'value_id' => '2',
                'file' => 'file_2.jpg',
                'media_type' => 'image',
                'position' => '0',
                'url' => 'url_to_the_placeholder/placeholder.jpg',
                'size' => 0
            ],
            [
                'value_id' => '1',
                'file' => 'file_1.jpg',
                'media_type' => 'image',
                'position' => '1',
                'url' => 'url_to_the_placeholder/placeholder.jpg',
                'size' => 0
            ]
        ];

        $images = [
            'images' => [
                [
                    'value_id' => '1',
                    'file' => 'file_1.jpg',
                    'media_type' => 'image',
                    'position' => '1'
                ],
                [
                    'value_id' => '2',
                    'file' => 'file_2.jpg',
                    'media_type' => 'image',
                    'position' => '0'
                ]
            ]
        ];

        $this->content->setElement($this->galleryMock);
        $this->galleryMock->expects($this->once())->method('getImages')->willReturn($images);
        $this->fileSystemMock->expects($this->any())->method('getDirectoryRead')->willReturn($this->readMock);
        $this->mediaConfigMock->expects($this->any())->method('getMediaUrl');
        $this->mediaConfigMock->expects($this->any())->method('getMediaPath');

        $this->readMock->expects($this->any())
            ->method('isFile')
            ->will($this->returnValue(true));
        $this->databaseMock->expects($this->any())
            ->method('checkDbUsage')
            ->will($this->returnValue(false));

        $this->readMock->expects($this->any())->method('stat')->willReturnOnConsecutiveCalls(
            $this->throwException(
                new \Magento\Framework\Exception\FileSystemException(new Phrase('test'))
            ),
            $this->throwException(
                new \Magento\Framework\Exception\FileSystemException(new Phrase('test'))
            )
        );
        $this->imageHelper->expects($this->any())->method('getDefaultPlaceholderUrl')->willReturn($placeholderUrl);
        $this->jsonEncoderMock->expects($this->once())->method('encode')->willReturnCallback('json_encode');

        $this->assertSame(json_encode($imagesResult), $this->content->getImagesJson());
    }

    /**
     * Test GetImageTypes() will return value for given attribute from data persistor.
     *
     * @return void
     */
    public function testGetImageTypesFromDataPersistor()
    {
        $attributeCode = 'thumbnail';
        $value = 'testImageValue';
        $scopeLabel = 'testScopeLabel';
        $label = 'testLabel';
        $name = 'testName';
        $expectedTypes = [
            $attributeCode => [
                'code' => $attributeCode,
                'value' => $value,
                'label' => $label,
                'name' => $name,
            ],
        ];
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())
            ->method('getData')
            ->with($this->identicalTo($attributeCode))
            ->willReturn(null);
        $mediaAttribute = $this->getMediaAttribute($label, $attributeCode);
        $product->expects($this->once())
            ->method('getMediaAttributes')
            ->willReturn([$mediaAttribute]);
        $this->galleryMock->expects($this->exactly(2))
            ->method('getDataObject')
            ->willReturn($product);
        $this->galleryMock->expects($this->once())
            ->method('getImageValue')
            ->with($this->identicalTo($attributeCode))
            ->willReturn($value);
        $this->galleryMock->expects($this->once())
            ->method('getScopeLabel')
            ->with($this->identicalTo($mediaAttribute))
            ->willReturn($scopeLabel);
        $this->galleryMock->expects($this->once())
            ->method('getAttributeFieldName')
            ->with($this->identicalTo($mediaAttribute))
            ->willReturn($name);
        $this->getImageTypesAssertions($attributeCode, $scopeLabel, $expectedTypes);
    }

    /**
     * Test GetImageTypes() will return value for given attribute from product.
     *
     * @return void
     */
    public function testGetImageTypesFromProduct()
    {
        $attributeCode = 'thumbnail';
        $value = 'testImageValue';
        $scopeLabel = 'testScopeLabel';
        $label = 'testLabel';
        $name = 'testName';
        $expectedTypes = [
            $attributeCode => [
                'code' => $attributeCode,
                'value' => $value,
                'label' => $label,
                'name' => $name,
            ],
        ];
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())
            ->method('getData')
            ->with($this->identicalTo($attributeCode))
            ->willReturn($value);
        $mediaAttribute = $this->getMediaAttribute($label, $attributeCode);
        $product->expects($this->once())
            ->method('getMediaAttributes')
            ->willReturn([$mediaAttribute]);
        $this->galleryMock->expects($this->exactly(2))
            ->method('getDataObject')
            ->willReturn($product);
        $this->galleryMock->expects($this->never())
            ->method('getImageValue');
        $this->galleryMock->expects($this->once())
            ->method('getScopeLabel')
            ->with($this->identicalTo($mediaAttribute))
            ->willReturn($scopeLabel);
        $this->galleryMock->expects($this->once())
            ->method('getAttributeFieldName')
            ->with($this->identicalTo($mediaAttribute))
            ->willReturn($name);
        $this->getImageTypesAssertions($attributeCode, $scopeLabel, $expectedTypes);
    }

    /**
     * Perform assertions.
     *
     * @param string $attributeCode
     * @param string $scopeLabel
     * @param array $expectedTypes
     * @return void
     */
    private function getImageTypesAssertions(string $attributeCode, string $scopeLabel, array $expectedTypes)
    {
        $this->content->setElement($this->galleryMock);
        $result = $this->content->getImageTypes();
        $scope = $result[$attributeCode]['scope'];
        $this->assertSame($scopeLabel, $scope->getText());
        unset($result[$attributeCode]['scope']);
        $this->assertSame($expectedTypes, $result);
    }

    /**
     * Get media attribute mock.
     *
     * @param string $label
     * @param string $attributeCode
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMediaAttribute(string $label, string $attributeCode)
    {
        $frontend = $this->getMockBuilder(Product\Attribute\Frontend\Image::class)
            ->disableOriginalConstructor()
            ->getMock();
        $frontend->expects($this->once())
            ->method('getLabel')
            ->willReturn($label);
        $mediaAttribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mediaAttribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $mediaAttribute->expects($this->once())
            ->method('getFrontend')
            ->willReturn($frontend);

        return $mediaAttribute;
    }

    /**
     * Test GetImagesJson() calls MediaStorage functions to obtain image from DB prior to stat call
     *
     * @return void
     */
    public function testGetImagesJsonMediaStorageMode()
    {
        $images = [
            'images' => [
                [
                    'value_id' => '0',
                    'file' => 'file_1.jpg',
                    'media_type' => 'image',
                    'position' => '0'
                ]
            ]
        ];

        $mediaPath = [
            ['file_1.jpg', 'catalog/product/image_1.jpg']
        ];

        $this->content->setElement($this->galleryMock);

        $this->galleryMock->expects($this->once())
            ->method('getImages')
            ->willReturn($images);
        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->willReturn($this->readMock);
        $this->mediaConfigMock->expects($this->any())
            ->method('getMediaPath')
            ->willReturnMap($mediaPath);

        $this->readMock->expects($this->any())
            ->method('isFile')
            ->will($this->returnValue(false));
        $this->databaseMock->expects($this->any())
            ->method('checkDbUsage')
            ->will($this->returnValue(true));

        $this->databaseMock->expects($this->once())
            ->method('saveFileToFilesystem')
            ->with('catalog/product/image_1.jpg');

        $this->content->getImagesJson();
    }
}
