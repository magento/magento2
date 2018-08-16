<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Gallery;

/**
 * Unit test for catalog product Media Gallery attribute processor.
 */
class ProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Gallery\Processor
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectHelper;

    /**
     * @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObject;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaDirectory;

    protected function setUp()
    {
        $this->objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->attributeRepository = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Attribute\Repository::class,
            ['get']
        );

        $fileStorageDb = $this->createMock(\Magento\MediaStorage\Helper\File\Storage\Database::class);

        $this->mediaConfig = $this->createMock(\Magento\Catalog\Model\Product\Media\Config::class);

        $this->mediaDirectory = $this->createMock(\Magento\Framework\Filesystem\Directory\Write::class);

        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($this->mediaDirectory);

        $resourceModel = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Gallery::class,
            ['getMainTable']
        );
        $resourceModel->expects($this->any())
            ->method('getMainTable')
            ->willReturn(
                \Magento\Catalog\Model\ResourceModel\Product\Gallery::GALLERY_TABLE
            );

        $this->dataObject = $this->createPartialMock(
            \Magento\Framework\DataObject::class,
            ['getIsDuplicate', 'isLockedAttribute', 'getMediaAttributes']
        );

        $this->model = $this->objectHelper->getObject(
            \Magento\Catalog\Model\Product\Gallery\Processor::class,
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
            \Magento\Eav\Model\Entity\Attribute::class,
            ['getBackendTable', 'isStatic', 'getAttributeId', 'getName', '__wakeup']
        );
        $attribute->expects($this->any())->method('getName')->will($this->returnValue('image'));
        $attribute->expects($this->any())->method('getAttributeId')->will($this->returnValue($attributeId));
        $attribute->expects($this->any())->method('isStatic')->will($this->returnValue(false));
        $attribute->expects($this->any())->method('getBackendTable')->will($this->returnValue('table'));

        $this->attributeRepository->expects($this->once())
            ->method('get')
            ->with('media_gallery')
            ->willReturn($attribute);

        $object = new \Magento\Framework\DataObject();
        $object->setImage(['images' => [['value_id' => $valueId]]]);
        $object->setId(555);

        $this->assertEquals(
            [
                \Magento\Catalog\Model\ResourceModel\Product\Gallery::GALLERY_TABLE => [
                    ['value_id' => $valueId, 'attribute_id' => 345345, 'entity_id' => $object->getId()],
                ],
            ],
            $this->model->getAffectedFields($object)
        );
    }

    /**
     * @dataProvider validateDataProvider
     * @param bool $value
     */
    public function testValidate($value)
    {
        $attributeCode = 'attr_code';
        $attribute = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute::class,
            ['getAttributeCode', 'getIsRequired', 'isValueEmpty', 'getIsUnique', 'getEntity', '__wakeup']
        );
        $attributeEntity = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\AbstractResource::class)
            ->setMethods(['checkAttributeUniqueValue'])
            ->getMockForAbstractClass();

        $attribute->expects($this->any())->method('getAttributeCode')->will($this->returnValue($attributeCode));
        $attribute->expects($this->any())->method('getIsRequired')->will($this->returnValue(true));
        $attribute->expects($this->any())->method('isValueEmpty')->will($this->returnValue($value));
        $attribute->expects($this->any())->method('getIsUnique')->will($this->returnValue(true));
        $attribute->expects($this->any())->method('getEntity')->will($this->returnValue($attributeEntity));
        $attributeEntity->expects($this->any())->method('checkAttributeUniqueValue')->will($this->returnValue(true));

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
    public function validateDataProvider()
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
     * @dataProvider clearMediaAttributeDataProvider
     */
    public function testClearMediaAttribute($setDataExpectsCalls, $setDataArgument, $mediaAttribute)
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

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
    public function clearMediaAttributeDataProvider()
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
