<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Category\Attribute\Backend;

use Magento\Catalog\Model\Category\Attribute\Backend\Image;
use Magento\Catalog\Model\ImageUploader;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for Magento\Catalog\Model\Category\Attribute\Backend\Image class.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageTest extends TestCase
{
    /**
     * @var AbstractAttribute
     */
    private $attribute;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var StoreManagerInterface|MockObject ;
     */
    private $storeManagerInterfaceMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->attribute = $this->getMockForAbstractClass(
            AbstractAttribute::class,
            [],
            'TestAttribute',
            false,
            false,
            true,
            ['getName']
        );

        $this->logger = $this->getMockForAbstractClass(
            LoggerInterface::class,
            [],
            'TestLogger',
            false,
            false,
            true,
            ['critical']
        );

        $this->imageUploader = $this->createPartialMock(
            ImageUploader::class,
            ['moveFileFromTmp', 'getBasePath']
        );

        $this->storeManagerInterfaceMock = $this->getMockBuilder(
            StoreManagerInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->storeMock = $this->getMockBuilder(
            Store::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return array
     */
    public function deletedValueDataProvider()
    {
        return [
            [false],
            [['delete' => true]]
        ];
    }

    /**
     * @dataProvider deletedValueDataProvider
     *
     * @param array $value
     */
    public function testBeforeSaveValueDeletion($value)
    {
        $this->attribute->expects($this->once())
            ->method('getName')
            ->willReturn('test_attribute');

        $model = $this->objectManager->getObject(Image::class);
        $model->setAttribute($this->attribute);

        $object = new DataObject(['test_attribute' => $value]);

        $model->beforeSave($object);

        $this->assertNull($object->getTestAttribute());
    }

    /**
     * @return array
     */
    public function invalidValueDataProvider()
    {
        $closure = function () {
            return false;
        };

        return [
            [1234],
            [true],
            [new \stdClass()],
            [$closure],
            [['a' => 1, 'b' => 2]]
        ];
    }

    /**
     * @dataProvider invalidValueDataProvider
     *
     * @param array $value
     */
    public function testBeforeSaveValueInvalid($value)
    {
        $this->attribute->expects($this->once())
            ->method('getName')
            ->willReturn('test_attribute');

        $model = $this->objectManager->getObject(Image::class);
        $model->setAttribute($this->attribute);

        $object = new DataObject(['test_attribute' => $value]);

        $model->beforeSave($object);

        $this->assertEquals('', $object->getTestAttribute());
    }

    /**
     * Test beforeSaveAttributeFileName.
     */
    public function testBeforeSaveAttributeFileName()
    {
        $this->setupObjectManagerForCheckImageExist(false);
        $this->attribute->expects($this->once())
            ->method('getName')
            ->willReturn('test_attribute');

        $model = $this->setUpModelForTests();
        $mediaDirectoryMock = $this->getMockForAbstractClass(WriteInterface::class);
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectoryMock);
        $this->imageUploader->expects($this->once())->method('getBasePath')->willReturn('base/path');
        $mediaDirectoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with('base/path/test123.jpg')
            ->willReturn('absolute/path/base/path/test123.jpg');

        $object = new DataObject(
            [
                'test_attribute' => [
                    ['name' => 'test123.jpg'],
                ],
            ]
        );

        $model->beforeSave($object);

        $this->assertEquals('test123.jpg', $object->getTestAttribute());
    }

    /**
     * Test beforeSaveAttributeFileNameOutsideOfCategoryDir.
     */
    public function testBeforeSaveAttributeFileNameOutsideOfCategoryDir()
    {
        $this->attribute->expects($this->once())
            ->method('getName')
            ->willReturn('test_attribute');

        $model = $this->setUpModelForTests();
        $model->setAttribute($this->attribute);
        $imagePath = '/pub/media/wysiwyg/test123.jpg';
        $this->filesystem
            ->expects($this->exactly(2))
            ->method('getUri')
            ->with(DirectoryList::MEDIA)
            ->willReturn('pub/media');

        $object = new DataObject(
            [
                'test_attribute' => [
                    [
                        'name' => 'test123.jpg',
                        'url' => $imagePath,
                    ],
                ],
            ]
        );

        $model->beforeSave($object);

        $this->assertEquals($imagePath, $object->getTestAttribute());
        $this->assertEquals(
            [['name' => $imagePath, 'url' => $imagePath]],
            $object->getData('_additional_data_test_attribute')
        );
    }

    private function setupObjectManagerForCheckImageExist($return)
    {
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $mockFileSystem = $this->createMock(Filesystem::class);
        $mockRead = $this->createMock(ReadInterface::class);
        $objectManagerMock->method($this->logicalOr('get', 'create'))->willReturn($mockFileSystem);
        $mockFileSystem->method('getDirectoryRead')->willReturn($mockRead);
        $mockRead->method('isExist')->willReturn($return);
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
    }

    /**
     * Test beforeSaveTemporaryAttribute.
     */
    public function testBeforeSaveTemporaryAttribute()
    {
        $this->setupObjectManagerForCheckImageExist(false);
        $this->attribute->expects($this->once())
            ->method('getName')
            ->willReturn('test_attribute');

        $this->storeManagerInterfaceMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock->expects($this->once())
            ->method('getBaseMediaDir')
            ->willReturn('media');

        $model = $this->setUpModelForTests();
        $model->setAttribute($this->attribute);

        $mediaDirectoryMock = $this->getMockForAbstractClass(WriteInterface::class);
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectoryMock);

        $mediaDirectoryMock->method('getAbsolutePath')->willReturn('/media/test123.jpg');

        $this->imageUploader->method('moveFileFromTmp')->willReturn('test123.jpg');

        $object = new DataObject(
            [
                'test_attribute' => [
                    [
                        'name' => 'test123.jpg',
                        'tmp_name' => 'abc123',
                        'url' => 'http://www.example.com/media/temp/test123.jpg'
                    ],
                ],
            ]
        );

        $model->beforeSave($object);

        $this->assertEquals(
            [
                ['name' => '/media/test123.jpg', 'tmp_name' => 'abc123', 'url' => '/media/test123.jpg'],
            ],
            $object->getData('_additional_data_test_attribute')
        );
    }

    /**
     * Test beforeSaveAttributeStringValue.
     */
    public function testBeforeSaveAttributeStringValue()
    {
        $this->attribute->method('getName')->willReturn('test_attribute_name');
        $model = $this->objectManager->getObject(Image::class);
        $model->setAttribute($this->attribute);

        $object = new DataObject(['test_attribute' => 'test123.jpg']);

        $model->beforeSave($object);

        $this->assertEquals('test123.jpg', $object->getTestAttribute());
        $this->assertNull($object->getData('_additional_data_test_attribute'));
    }

    /**
     * @return Image
     */
    private function setUpModelForTests()
    {
        $objectManagerMock = $this->createPartialMock(\Magento\Framework\App\ObjectManager::class, ['get']);

        $imageUploaderMock = $this->imageUploader;

        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnCallback(
                function ($class, $params = []) use ($imageUploaderMock) {
                    if ($class == "\Magento\Catalog\CategoryImageUpload") {
                        return $imageUploaderMock;
                    }

                    return $this->objectManager->get($class, $params);
                }
            );

        $model = $this->objectManager->getObject(
            Image::class,
            [
                'objectManager' => $objectManagerMock,
                'logger' => $this->logger,
                'filesystem' => $this->filesystem,
                'storeManager' => $this->storeManagerInterfaceMock
            ]
        );
        $this->objectManager->setBackwardCompatibleProperty($model, 'imageUploader', $this->imageUploader);

        return $model->setAttribute($this->attribute);
    }

    /**
     * @return array
     */
    public function attributeValueDataProvider()
    {
        return [
            [[['name' => 'test1234.jpg']]],
            ['test1234.jpg'],
            [''],
            [false]
        ];
    }

    /**
     * @dataProvider attributeValueDataProvider
     *
     * @param array $value
     * @throws FileSystemException
     */
    public function testBeforeSaveWithAdditionalData($value)
    {
        $this->attribute->method('getName')->willReturn('test_attribute_name');
        $model = $this->setUpModelForTests();

        $this->imageUploader->expects($this->never())
            ->method('moveFileFromTmp')
            ->with($this->equalTo('test1234.jpg'));

        $object = new DataObject(
            [
                'test_attribute' => $value,
                '_additional_data_test_attribute' => [['name' => 'test1234.jpg', 'tmp_name' => 'test-test-1234']]
            ]
        );

        $model->beforeSave($object);
    }

    /**
     * @dataProvider attributeValueDataProvider
     *
     * @param array $value
     * @throws FileSystemException
     */
    public function testBeforeSaveWithoutAdditionalData($value)
    {
        $this->attribute->method('getName')->willReturn('test_attribute_name');
        $model = $this->setUpModelForTests();

        $this->imageUploader->expects($this->never())
            ->method('moveFileFromTmp');

        $object = new DataObject(
            [
                'test_attribute' => $value
            ]
        );

        $model->beforeSave($object);
    }

    /**
     * Test afterSaveWithExceptions.
     */
    public function testBeforeSaveWithExceptions()
    {
        $this->setupObjectManagerForCheckImageExist(false);
        $model = $this->setUpModelForTests();

        $this->storeManagerInterfaceMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock->expects($this->once())
            ->method('getBaseMediaDir')
            ->willReturn('pub/media');

        $this->attribute->expects($this->once())
            ->method('getName')
            ->willReturn('_additional_data_test_attribute');

        $mediaDirectoryMock = $this->getMockForAbstractClass(WriteInterface::class);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectoryMock);
        $this->imageUploader->expects($this->any())->method('getBasePath')->willReturn('base/path');
        $mediaDirectoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->with('base/path/test1234.jpg')
            ->willReturn('absolute/path/base/path/test1234.jpg');

        $exception = new \Exception();

        $this->imageUploader->expects($this->any())
            ->method('moveFileFromTmp')
            ->will($this->throwException($exception));

        $this->logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo($exception));

        $object = new DataObject(
            [
                '_additional_data_test_attribute' => [['name' => 'test1234.jpg', 'tmp_name' => 'test-test-1234']]
            ]
        );

        $model->beforeSave($object);
    }
}
