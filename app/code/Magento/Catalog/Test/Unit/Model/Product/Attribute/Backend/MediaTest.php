<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend;

class MediaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Media
     */
    protected $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectHelper;

    /**
     * @var \Magento\Framework\DataObject | \PHPUnit_Framework_MockObject_MockObject
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
        $this->_objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);

        $fileStorageDb = $this->getMock('Magento\MediaStorage\Helper\File\Storage\Database', [], [], '', false);
        $this->resourceModel = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Media',
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

        $this->productFactory = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\ProductFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->model = $this->_objectHelper->getObject(
            'Magento\Catalog\Model\Product\Attribute\Backend\Media',
            [
                'productFactory' => $this->productFactory,
                'eventManager' => $eventManager,
                'fileStorageDb' => $fileStorageDb,
                'mediaConfig' => $this->mediaConfig,
                'filesystem' => $filesystem,
                'resourceProductAttribute' => $this->resourceModel
            ]
        );
        $this->dataObject = $this->getMockBuilder('Magento\Framework\DataObject')
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

        $object = new \Magento\Framework\DataObject();
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
            '\Magento\Framework\Model\ResourceModel\AbstractResourceAbstractEntity',
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
}
