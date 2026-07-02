<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Gallery;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Helper\File\Storage\Database;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for catalog product Media Gallery attribute processor.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessorTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Processor
     */
    protected $model;

    /**
     * @var Repository|MockObject
     */
    protected $attributeRepository;

    /**
     * @var ObjectManager
     */
    protected $objectHelper;

    /**
     * @var DataObject|MockObject
     */
    protected $dataObject;

    /**
     * @var Config|MockObject
     */
    protected $mediaConfig;

    /**
     * @var Write|MockObject
     */
    protected $mediaDirectory;

    protected function setUp(): void
    {
        $this->objectHelper = new ObjectManager($this);

        $this->attributeRepository = $this->createPartialMock(
            Repository::class,
            ['get']
        );

        $fileStorageDb = $this->createMock(Database::class);

        $this->mediaConfig = $this->createMock(Config::class);

        $this->mediaDirectory = $this->createMock(Write::class);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($this->mediaDirectory);

        $resourceModel = $this->createPartialMock(
            Gallery::class,
            ['getMainTable']
        );
        $resourceModel->method('getMainTable')->willReturn(
            Gallery::GALLERY_TABLE
        );

        $this->dataObject = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getData', 'setData', 'setMainTable']
        );
        $dataStore = [];
        $this->dataObject->method('getData')->willReturnCallback(function ($key = null) use (&$dataStore) {
            return $key === null ? $dataStore : ($dataStore[$key] ?? null);
        });
        $this->dataObject->method('setData')->willReturnCallback(function ($key, $value = null) use (&$dataStore) {
            if (is_array($key)) {
                $dataStore = $key;
            } else {
                $dataStore[$key] = $value;
            }
            return $this->dataObject;
        });
        $this->dataObject->method('setMainTable')->willReturnSelf();

        $this->model = $this->objectHelper->getObject(
            Processor::class,
            [
                'attributeRepository' => $this->attributeRepository,
                'fileStorageDb' => $fileStorageDb,
                'mediaConfig' => $this->mediaConfig,
                'filesystem' => $filesystem,
                'resourceModel' => $resourceModel
            ]
        );
    }

    public function testGetAffectedFields()
    {
        $valueId = 2345;
        $attributeId = 345345;

        $attribute = $this->createPartialMock(
            Attribute::class,
            ['getBackendTable', 'isStatic', 'getAttributeId', 'getName']
        );
        $attribute->method('getName')->willReturn('image');
        $attribute->method('getAttributeId')->willReturn($attributeId);
        $attribute->method('isStatic')->willReturn(false);
        $attribute->method('getBackendTable')->willReturn('table');

        $this->attributeRepository->expects($this->once())
            ->method('get')
            ->with('media_gallery')
            ->willReturn($attribute);

        $object = new DataObject();
        $object->setImage(['images' => [['value_id' => $valueId]]]);
        $object->setId(555);

        $this->assertEquals(
            [
                Gallery::GALLERY_TABLE => [
                    ['value_id' => $valueId, 'attribute_id' => 345345, 'entity_id' => $object->getId()],
                ],
            ],
            $this->model->getAffectedFields($object)
        );
    }

    /**
     * @param bool $value
     */
    #[DataProvider('validateDataProvider')]
    public function testValidate($value)
    {
        $attributeCode = 'attr_code';
        $attribute = $this->createPartialMock(
            Attribute::class,
            ['getAttributeCode', 'getIsRequired', 'isValueEmpty', 'getIsUnique', 'getEntity']
        );
        $attributeEntity = $this->createPartialMockWithReflection(
            AbstractResource::class,
            ['setCheckAttributeUniqueValueResult', 'checkAttributeUniqueValue', '_construct', 'getConnection']
        );
        $checkResult = true;
        $attributeEntity->method('setCheckAttributeUniqueValueResult')->willReturnCallback(
            function ($value) use (&$checkResult) {
                $checkResult = $value;
            }
        );
        $attributeEntity->method('checkAttributeUniqueValue')->willReturnCallback(
            function () use (&$checkResult) {
                return $checkResult;
            }
        );
        $attributeEntity->method('_construct')->willReturn(null);
        $attributeEntity->method('getConnection')->willReturn(null);

        $attribute->method('getAttributeCode')->willReturn($attributeCode);
        $attribute->method('getIsRequired')->willReturn(true);
        $attribute->method('isValueEmpty')->willReturn($value);
        $attribute->method('getIsUnique')->willReturn(true);
        $attribute->method('getEntity')->willReturn($attributeEntity);
        $attributeEntity->setCheckAttributeUniqueValueResult(true);

        $this->attributeRepository->expects($this->once())
            ->method('get')
            ->with('media_gallery')
            ->willReturn($attribute);

        $this->dataObject->setData(['attr_code' => 'attribute data']);
        $this->assertEquals(!$value, $this->model->validate($this->dataObject));
    }

    /**
     * @return array
     */
    public static function validateDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @param int $setDataExpectsCalls
     * @param string|null $setDataArgument
     * @param array|string $mediaAttribute
     */
    #[DataProvider('clearMediaAttributeDataProvider')]
    public function testClearMediaAttribute($setDataExpectsCalls, $setDataArgument, $mediaAttribute)
    {
        $productMock = $this->createMock(Product::class);

        $productMock->expects($this->exactly($setDataExpectsCalls))
            ->method('setData')
            ->with($setDataArgument, 'no_selection');

        $this->mediaConfig->expects($this->once())
            ->method('getMediaAttributeCodes')
            ->willReturn(['image', 'small_image']);

        $this->assertSame($this->model, $this->model->clearMediaAttribute($productMock, $mediaAttribute));
    }

    /**
     * @return array
     */
    public static function clearMediaAttributeDataProvider()
    {
        return [
            [
                'setDataExpectsCalls' => 1,
                'setDataArgument' => 'image',
                'mediaAttribute' => 'image',
            ],
            [
                'setDataExpectsCalls' => 1,
                'setDataArgument' => 'image',
                'mediaAttribute' => ['image'],
            ],
            [
                'setDataExpectsCalls' => 0,
                'setDataArgument' => null,
                'mediaAttribute' => 'some_image',
            ],
            [
                'setDataExpectsCalls' => 0,
                'setDataArgument' => null,
                'mediaAttribute' => ['some_image'],
            ],
        ];
    }
}
