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
                'deleteGallery'
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
        $filesystem = $this->getMockBuilder('Magento\Framework\App\Filesystem')
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
            ->setMethods(['getIsDuplicate', 'isLockedAttribute'])
            ->getMock();
    }

    public function testGetAffectedFields()
    {
        $valueId = 2345;
        $attributeId = 345345;

        $attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
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
                    ['value_id' => $valueId, 'attribute_id' => $attributeId, 'entity_id' => $object->getId()]
                ]
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
                    'file' => 'file/path'
                ],
                [
                    'removed' => false,
                    'value_id' => 1,
                    'file' => 'file/path2'
                ]
            ]
        ];
        $assignedImages = [
            ['filepath' => 'path_to_image']
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
}
