<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

class MediaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Media
     */
    protected $model;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectHelper;

    /**
     * @var \Magento\Framework\Object | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObject;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaDirectory;

    protected function setUp()
    {
        $this->_objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);

        $fileStorageDb = $this->getMock('Magento\Core\Helper\File\Storage\Database', [], [], '', false);
        $coreData = $this->getMock('Magento\Core\Helper\Data', [], [], '', false);
        $this->resourceModel = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media',
            [
                'getMainTable',
                '__wakeup',
                'insertGallery',
                'deleteGalleryValueInStore',
                'insertGalleryValueInStore',
                'deleteGallery',
                'loadGallery'
            ],
            [],
            '',
            false
        );
        $this->resourceModel->expects($this->any())->method('getMainTable')->will($this->returnValue('table'));

        $this->mediaConfig = $this->getMock('Magento\Catalog\Model\Product\Media\Config', [], [], '', false);
        $this->mediaDirectory = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\Write')
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem->expects($this->once())->method('getDirectoryWrite')->will(
            $this->returnValue($this->mediaDirectory)
        );

        $this->productFactory = $this->getMockBuilder('Magento\Catalog\Model\Resource\ProductFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->model = $this->_objectHelper->getObject(
            'Magento\Catalog\Model\Product\Attribute\Backend\Media',
            [
                'productFactory' => $this->productFactory,
                'eventManager' => $eventManager,
                'fileStorageDb' => $fileStorageDb,
                'coreData' => $coreData,
                'mediaConfig' => $this->mediaConfig,
                'filesystem' => $filesystem,
                'resourceProductAttribute' => $this->resourceModel
            ]
        );
        $this->dataObject = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(['getIsDuplicate', 'isLockedAttribute', 'getMediaAttributes'])
            ->getMock();
    }

    public function testGetAffectedFields()
    {
        $valueId = 2345;
        $attributeId = 345345;

        $attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute',
            ['getBackendTable', 'isStatic', 'getAttributeId', 'getName', '__wakeup'],
            [],
            '',
            false
        );
        $attribute->expects($this->any())->method('getName')->will($this->returnValue('image'));
        $attribute->expects($this->any())->method('getAttributeId')->will($this->returnValue($attributeId));
        $attribute->expects($this->any())->method('isStatic')->will($this->returnValue(false));
        $attribute->expects($this->any())->method('getBackendTable')->will($this->returnValue('table'));

        $this->model->setAttribute($attribute);

        $object = new \Magento\Framework\Object();
        $object->setImage(['images' => [['value_id' => $valueId]]]);
        $object->setId(555);

        $this->assertEquals(
            [
                'table' => [
                    ['value_id' => $valueId, 'attribute_id' => $attributeId, 'entity_id' => $object->getId()],
                ],
            ],
            $this->model->getAffectedFields($object)
        );
    }

    public function testAfterSaveDuplicate()
    {
        $attributeCode = 'test_code';
        $attributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));

        $this->dataObject->expects($this->once())
            ->method('getIsDuplicate')
            ->will($this->returnValue(true));
        $this->dataObject->setData($attributeCode, []);

        $this->model->setAttribute($attributeMock);
        $this->assertNull($this->model->afterSave($this->dataObject));
    }

    public function testAfterSaveNoAttribute()
    {
        $attributeCode = 'test_code';
        $attributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));

        $this->dataObject->expects($this->once())
            ->method('getIsDuplicate')
            ->will($this->returnValue(false));
        $this->dataObject->setData($attributeCode, []);

        $this->model->setAttribute($attributeMock);
        $this->assertNull($this->model->afterSave($this->dataObject));
    }

    public function testAfterSaveDeleteFiles()
    {
        $storeId = 1;
        $storeIds = ['store_1' => 1, 'store_2' => 2];
        $attributeCode = 'test_code';
        $toDelete = [1];
        $mediaPath = 'catalog/media';
        $filePathToRemove = $mediaPath . '/file/path';
        $attributeValue = [
            'images' => [
                [
                    'removed' => true,
                    'value_id' => 1,
                    'file' => 'file/path',
                ],
                [
                    'removed' => false,
                    'value_id' => 1,
                    'file' => 'file/path2'
                ],
            ],
        ];
        $assignedImages = [
            ['filepath' => 'path_to_image'],
        ];

        $attributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));

        $this->dataObject->expects($this->once())
            ->method('getIsDuplicate')
            ->will($this->returnValue(false));
        $this->dataObject->expects($this->once())
            ->method('isLockedAttribute')
            ->will($this->returnValue(false));
        $this->dataObject->setData($attributeCode, $attributeValue);
        $this->dataObject->setId(1);
        $this->dataObject->setStoreId($storeId);
        $this->dataObject->setStoreIds($storeIds);

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getAssignedImages', '__wakeup'])
            ->getMock();
        $productMock->expects($this->any())
            ->method('getAssignedImages')
            ->will($this->returnValue($assignedImages));

        $this->productFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($productMock));

        $this->resourceModel->expects($this->once())
            ->method('deleteGallery')
            ->with($toDelete);

        $this->mediaConfig->expects($this->once())
            ->method('getBaseMediaPath')
            ->will($this->returnValue($mediaPath));

        $this->mediaDirectory->expects($this->once())
            ->method('delete')
            ->with($filePathToRemove);

        $this->model->setAttribute($attributeMock);
        $this->assertNull($this->model->afterSave($this->dataObject));
    }

    /**
     * @dataProvider afterLoadDataProvider
     * @param array $image
     */
    public function testAfterLoad($image)
    {
        $attributeCode = 'attr_code';
        $attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute',
            ['getAttributeCode', '__wakeup'],
            [],
            '',
            false
        );
        $attribute->expects($this->any())->method('getAttributeCode')->will($this->returnValue($attributeCode));
        $this->resourceModel->expects($this->any())->method('loadGallery')->will($this->returnValue([$image]));

        $this->model->setAttribute($attribute);
        $this->model->afterLoad($this->dataObject);
        $this->assertEquals([$image], $this->dataObject->getAttrCode('images'));
    }

    public function afterLoadDataProvider()
    {
        return [
            [
                [
                    'label' => 'label_1',
                    'position' => 'position_1',
                    'disabled' => 'true',
                ],
                [
                    'label' => 'label_2',
                    'position' => 'position_2',
                    'disabled' => 'true'
                ],
            ],
            [
                [
                    'label' => null,
                    'position' => null,
                    'disabled' => null,
                ],
                [
                    'label' => null,
                    'position' => null,
                    'disabled' => null
                ]
            ]
        ];
    }

    /**
     * @dataProvider validateDataProvider
     * @param bool $value
     */
    public function testValidate($value)
    {
        $attributeCode = 'attr_code';
        $attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute',
            ['getAttributeCode', 'getIsRequired', 'isValueEmpty', 'getIsUnique', 'getEntityType', '__wakeup'],
            [],
            '',
            false
        );
        $attributeEntity = $this->getMock(
            '\Magento\Framework\Model\Resource\AbstractResourceAbstractEntity',
            ['checkAttributeUniqueValue']
        );
        $attribute->expects($this->any())->method('getAttributeCode')->will($this->returnValue($attributeCode));
        $attribute->expects($this->any())->method('getIsRequired')->will($this->returnValue(true));
        $attribute->expects($this->any())->method('isValueEmpty')->will($this->returnValue($value));
        $attribute->expects($this->any())->method('getIsUnique')->will($this->returnValue(true));
        $attribute->expects($this->any())->method('getEntityType')->will($this->returnValue($attributeEntity));
        $attributeEntity->expects($this->any())->method('checkAttributeUniqueValue')->will($this->returnValue(true));

        $this->model->setAttribute($attribute);
        $this->dataObject->setData(['attr_code' => 'attribute data']);
        $this->assertEquals(!$value, $this->model->validate($this->dataObject));
    }

    public function validateDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider beforeSaveDataProvider
     * @param array $value
     */
    public function testBeforeSave($value)
    {
        $attributeCode = 'attr_code';
        $attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute',
            ['getAttributeCode', 'getIsRequired', 'isValueEmpty', 'getIsUnique', 'getEntityType', '__wakeup'],
            [],
            '',
            false
        );
        $mediaAttributes = [
            'image' => $attribute,
            'small_image' => $attribute,
            'thumbnail' => $attribute,
        ];
        $attribute->expects($this->any())->method('getAttributeCode')->will($this->returnValue($attributeCode));
        $this->dataObject->expects($this->any())->method('getIsDuplicate')->will($this->returnValue(false));
        $this->model->setAttribute($attribute);
        $this->dataObject->setData(['attr_code' => ['images' => $value]]);
        $this->dataObject->expects($this->any())->method('getMediaAttributes')
            ->will(($this->returnValue($mediaAttributes)));
        $this->model->beforeSave($this->dataObject);
        foreach ($this->dataObject['attr_code']['images'] as $imageType => $imageData) {
            if (isset($imageData['new_file'])) {
                $value[$imageType]['file'] = $imageData['file'];
                $value[$imageType]['new_file'] = $imageData['new_file'];
            }
            $this->assertEquals($value[$imageType], $imageData);
        }
    }

    public function beforeSaveDataProvider()
    {
        return [
            [
                [
                    'image_1' => [
                        'position' => '1',
                        'file' => '/m/y/mydrawing1.jpg.tmp',
                        'value_id' => '',
                        'label' => 'image 1',
                        'disableed' => '0',
                        'removed' => '',
                    ],
                    'image_2' => [
                        'position' => '1',
                        'file' => '/m/y/mydrawing2.jpg.tmp',
                        'value_id' => '',
                        'label' => 'image 2',
                        'disableed' => '0',
                        'removed' => '',
                    ],
                    'image_removed' => [
                        'position' => '1',
                        'file' => '/m/y/mydrawing3.jpg.tmp',
                        'value_id' => '',
                        'label' => 'image 3',
                        'disableed' => '0',
                        'removed' => '1',
                    ],
                ],
            ]
        ];
    }
}
