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

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

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

        $this->logger = $this->getMockForAbstractClass(
            \Psr\Log\LoggerInterface::class,
            [],
            'TestLogger',
            false,
            false,
            true,
            ['critical']
        );

        $this->imageUploader = $this->getMock(
            \Magento\Catalog\Model\ImageUploader::class,
            ['moveFileFromTmp'],
            [],
            '',
            false
        );
    }

    public function deletionValueProvider()
    {
        return [
            [false],
            [['delete' => true]]
        ];
    }

    /**
     * @dataProvider deletionValueProvider
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

    public function invalidValueProvider()
    {
        return [
            [1234],
            [true],
            [new \stdClass()],
            [function() {}],
            [['a' => 1, 'b' => 2]]
        ];
    }

    /**
     * @dataProvider invalidValueProvider
     *
     * @param $value
     */
    public function testBeforeSaveShouldSetAttributeValueToBlankWhenImageValueInvalid($value)
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
        ], $object->getData('_additional_data_test_attribute'));
    }

    public function testBeforeSaveShouldNotModifyAttributeValueWhenStringValue()
    {
        $model = $this->objectManager->getObject(Model::class);
        $model->setAttribute($this->attribute);

        $object = new \Magento\Framework\DataObject([
            'test_attribute' => 'test123.jpg'
        ]);

        $model->beforeSave($object);

        $this->assertEquals('test123.jpg', $object->getTestAttribute());
    }

    public function testBeforeSaveShouldNotSetAdditionalDataWhenStringValue()
    {
        $model = $this->objectManager->getObject(Model::class);
        $model->setAttribute($this->attribute);

        $object = new \Magento\Framework\DataObject([
            'test_attribute' => 'test123.jpg'
        ]);

        $model->beforeSave($object);

        $this->assertNull($object->getData('_additional_data_test_attribute'));
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
            ->will($this->returnCallback(function ($class, $params = []) use ($imageUploaderMock) {
                if ($class == \Magento\Catalog\CategoryImageUpload::class) {
                    return $imageUploaderMock;
                }

                return $this->objectManager->get($class, $params);
            }));

        $model = $this->objectManager->getObject(Model::class, [
            'objectManager' => $objectManagerMock,
            'logger' => $this->logger
        ]);

        return $model->setAttribute($this->attribute);
    }

    public function attributeValidValueProvider()
    {
        return [
            [[['name' => 'test1234.jpg']]],
            ['test1234.jpg'],
            [''],
            [false]
        ];
    }

    /**
     * @dataProvider attributeValidValueProvider
     *
     * @param $value
     */
    public function testAfterSaveShouldUploadImageWhenAdditionalDataSet($value)
    {
        $model = $this->setUpModelForAfterSave();

        $this->imageUploader->expects($this->once())
            ->method('moveFileFromTmp')
            ->with($this->equalTo('test1234.jpg'));

        $object = new \Magento\Framework\DataObject([
            'test_attribute' => $value,
            '_additional_data_test_attribute' => [
                ['name' => 'test1234.jpg']
            ]
        ]);

        $model->afterSave($object);
    }

    /**
     * @dataProvider attributeValidValueProvider
     *
     * @param $value
     */
    public function testAfterSaveShouldNotUploadImageWhenAdditionalDataNotSet($value)
    {
        $model = $this->setUpModelForAfterSave();

        $this->imageUploader->expects($this->never())
            ->method('moveFileFromTmp');

        $object = new \Magento\Framework\DataObject([
            'test_attribute' => $value
        ]);

        $model->afterSave($object);
    }

    public function testAfterSaveShouldCreateCriticalLogEntryOnUploadExceptions()
    {
        $model = $this->setUpModelForAfterSave();

        $exception = new \Exception();

        $this->imageUploader->expects($this->any())
            ->method('moveFileFromTmp')
            ->will($this->throwException($exception));

        $this->logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo($exception));

        $object = new \Magento\Framework\DataObject([
            '_additional_data_test_attribute' => [
                ['name' => 'test1234.jpg']
            ]
        ]);

        $model->afterSave($object);
    }
}
