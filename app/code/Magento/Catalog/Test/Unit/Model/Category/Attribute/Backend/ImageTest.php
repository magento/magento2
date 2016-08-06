<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Category\Attribute\Backend;

use \Magento\Catalog\Model\Category\Attribute\Backend\Image as Model;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    protected $attribute;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\ImageUploader
     */
    protected $imageUploader;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->attribute = $this->getMockForAbstractClass(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            [],
            'TestAttribute',
            false,
            false,
            true,
            ['getName']
        );

        $this->attribute->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('test_attribute'));

        $this->imageUploader = $this->getMock(
            \Magento\Catalog\Model\ImageUploader::class,
            ['moveFileFromTmp'],
            [],
            '',
            false
        );
    }

    public function deletionValuesProvider()
    {
        return [
            [false],
            [['delete' => true]]
        ];
    }

    /**
     * @dataProvider deletionValuesProvider
     *
     * @param $value
     */
    public function testBeforeSaveShouldSetAttributeValueToBlankWhenImageValueRequiresDeletion($value)
    {
        $model = $this->objectManager->getObject(Model::class);
        $model->setAttribute($this->attribute);

        $object = new \Magento\Framework\DataObject([
            'test_attribute' => $value
        ]);

        $model->beforeSave($object);

        $this->assertEquals('', $object->getTestAttribute());
    }

    public function testBeforeSaveShouldSetAttributeValueToUploadedImageName()
    {
        $model = $this->objectManager->getObject(Model::class);
        $model->setAttribute($this->attribute);

        $object = new \Magento\Framework\DataObject([
            'test_attribute' => [
                ['name' => 'test123.jpg']
            ]
        ]);

        $model->beforeSave($object);

        $this->assertEquals('test123.jpg', $object->getTestAttribute());
    }

    public function testBeforeSaveShouldSetAttributeUploadInformationToTemporaryAttribute()
    {
        $model = $this->objectManager->getObject(Model::class);
        $model->setAttribute($this->attribute);

        $object = new \Magento\Framework\DataObject([
            'test_attribute' => [
                ['name' => 'test123.jpg', 'tmp_name' => 'abc123', 'url' => 'http://www.test.com/test123.jpg']
            ]
        ]);

        $model->beforeSave($object);

        $this->assertEquals([
            ['name' => 'test123.jpg', 'tmp_name' => 'abc123', 'url' => 'http://www.test.com/test123.jpg']
        ], $object->getTestAttributeAdditionalData());
    }

    public function stringValueProvider()
    {
        return [
            ['test123'],
            [12345],
            [true],
            ['some' => 'value']
        ];
    }

    /**
     * @dataProvider stringValueProvider
     *
     * @param $value
     */
    public function testBeforeSaveShouldNotModifyAttributeValueWhenNotUploadData($value)
    {
        $model = $this->objectManager->getObject(Model::class);
        $model->setAttribute($this->attribute);

        $object = new \Magento\Framework\DataObject([
            'test_attribute' => $value
        ]);

        $model->beforeSave($object);

        $this->assertEquals($value, $object->getTestAttribute());
    }

    /**
     * @dataProvider stringValueProvider
     *
     * @param $value
     */
    public function testBeforeSaveShouldNotSetAdditionalDataWhenNotUploadData($value)
    {
        $model = $this->objectManager->getObject(Model::class);
        $model->setAttribute($this->attribute);

        $object = new \Magento\Framework\DataObject([
            'test_attribute' => $value
        ]);

        $model->beforeSave($object);

        $this->assertNull($object->getTestAttributeAdditionalData());
    }

    protected function setUpModelForAfterSave()
    {
        $objectManagerMock = $this->getMock(
            \Magento\Framework\App\ObjectManager::class,
            ['get'],
            [],
            '',
            false
        );

        $imageUploaderMock = $this->imageUploader;

        $objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function($class, $params = []) use ($imageUploaderMock) {
                if ($class == \Magento\Catalog\CategoryImageUpload::class) {
                    return $imageUploaderMock;
                }

                return $this->objectManager->get($class, $params);
            }));

        $model = $this->objectManager->getObject(Model::class, [
            'objectManager' => $objectManagerMock
        ]);

        return $model->setAttribute($this->attribute);
    }

    public function testAfterSaveShouldUploadImageWhenAdditionalDataSet()
    {
        $model = $this->setUpModelForAfterSave();

        $this->imageUploader->expects($this->once())
            ->method('moveFileFromTmp')
            ->with($this->equalTo('test1234.jpg'));

        $object = new \Magento\Framework\DataObject([
            'test_attribute_additional_data' => [
                ['name' => 'test1234.jpg']
            ]
        ]);

        $model->afterSave($object);
    }

    public function testAfterSaveShouldNotUploadImageWhenAdditionalDataNotSet()
    {
        $model = $this->setUpModelForAfterSave();

        $this->imageUploader->expects($this->never())
            ->method('moveFileFromTmp');

        $object = new \Magento\Framework\DataObject([
            'test_attribute' => [
                ['name' => 'test1234.jpg']
            ]
        ]);

        $model->afterSave($object);
    }
}
