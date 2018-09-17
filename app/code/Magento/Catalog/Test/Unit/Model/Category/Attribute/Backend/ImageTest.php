<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Category\Attribute\Backend;

use Magento\Catalog\Model\Category\Attribute\Backend\Image;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Psr\Log\LoggerInterface;

/**
 * Class ImageTest @covers \Magento\Catalog\Model\Category\Attribute\Backend\Image.
 */
class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * AbstractAttribute mock holder.
     *
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attribute;

    /**
     * Object Manager instance holder.
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * ImageUploder mock holder.
     *
     * @var ImageUploader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $imageUploader;

    /**
     * LoggerInterface mock holder.
     *
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * Prepare subject fot tests.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
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
            LoggerInterface::class,
            [],
            'TestLogger',
            false,
            false,
            true,
            ['critical']
        );
        $this->imageUploader = $this->getMockBuilder(ImageUploader::class)
            ->disableOriginalConstructor()
            ->setMethods(['moveFileFromTmp'])
            ->getMock();
    }

    /**
     * Test Image::beforeSave() returns empty string on attribute removal.
     *
     * @dataProvider deletedValueDataProvider
     * @param array $value
     * @return void
     */
    public function testBeforeSaveValueDeletion($value)
    {
        $model = $this->objectManager->getObject(Image::class);
        $model->setAttribute($this->attribute);
        $object = new \Magento\Framework\DataObject([
            'test_attribute' => $value
        ]);
        $model->beforeSave($object);
        $this->assertEquals('', $object->getTestAttribute());
    }

    /**
     * Test Image::beforeSave() with invalid attribute value returns empty string.
     *
     * @dataProvider invalidValueDataProvider
     * @param array $value
     * @return void
     */
    public function testBeforeSaveValueInvalid($value)
    {
        $model = $this->objectManager->getObject(Image::class);
        $model->setAttribute($this->attribute);
        $object = new \Magento\Framework\DataObject([
            'test_attribute' => $value
        ]);
        $model->beforeSave($object);
        $this->assertEquals('', $object->getTestAttribute());
    }

    /**
     * Test Image::beforeSave() save attribute image name.
     *
     * @return void
     */
    public function testBeforeSaveAttributeFileName()
    {
        $model = $this->objectManager->getObject(Image::class);
        $model->setAttribute($this->attribute);
        $object = new \Magento\Framework\DataObject([
            'test_attribute' => [
                ['name' => 'test123.jpg']
            ]
        ]);
        $model->beforeSave($object);
        $this->assertEquals('test123.jpg', $object->getTestAttribute());
    }

    /**
     * Test Image::beforeSave() can handle attribute value as string.
     *
     * @return void
     */
    public function testBeforeSaveAttributeStringValue()
    {
        $model = $this->objectManager->getObject(Image::class);
        $model->setAttribute($this->attribute);
        $object = new \Magento\Framework\DataObject([
            'test_attribute' => 'test123.jpg'
        ]);
        $model->beforeSave($object);
        $this->assertEquals('test123.jpg', $object->getTestAttribute());
    }

    /**
     * Test Image::afterSave().
     *
     * @return void
     */
    public function testAfterSave()
    {
        $model = $this->setUpModelForAfterSave();
        $this->imageUploader->expects($this->once())
            ->method('moveFileFromTmp')
            ->with($this->equalTo('test1234.jpg'));

        $object = new \Magento\Framework\DataObject(
            [
                'test_attribute' => 'test1234.jpg'
            ]
        );
        $model->afterSave($object);
    }

    /**
     * Test Image::afterSave() with invalid attribute value.
     *
     * @dataProvider invalidValueDataProviderForAfterSave
     * @param array $value
     * @return void
     */
    public function testAfterSaveValueInvalid($value)
    {
        $model = $this->setUpModelForAfterSave();
        $this->imageUploader->expects($this->never())
            ->method('moveFileFromTmp');
        $object = new \Magento\Framework\DataObject(
            [
                'test_attribute' => $value
            ]
        );
        $model->afterSave($object);
    }

    /**
     * Test Image::afterSave() log error on exception.
     *
     * @return void
     */
    public function testAfterSaveWithExceptions()
    {
        $model = $this->setUpModelForAfterSave();
        $exception = new \Exception();
        $this->imageUploader->expects($this->any())
            ->method('moveFileFromTmp')
            ->will($this->throwException($exception));
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo($exception));
        $object = new \Magento\Framework\DataObject(
            [
                'test_attribute' => 'test1234.jpg'
            ]
        );
        $model->afterSave($object);
    }

    /**
     * Prepare Image for Image::afterSave().
     *
     * @return Image
     */
    private function setUpModelForAfterSave()
    {
        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $imageUploaderMock = $this->imageUploader;

        $objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($class, $params = []) use ($imageUploaderMock) {
                if ($class == \Magento\Catalog\CategoryImageUpload::class) {
                    return $imageUploaderMock;
                }

                return $this->objectManager->get($class, $params);
            }));

        $model = $this->objectManager->getObject(Image::class, [
            'objectManager' => $objectManagerMock,
            'logger' => $this->logger
        ]);
        $this->objectManager->setBackwardCompatibleProperty($model, 'imageUploader', $this->imageUploader);

        return $model->setAttribute($this->attribute);
    }

    /**
     * Test data for testAfterSaveValueInvalid().
     *
     * @return array
     */
    public function invalidValueDataProviderForAfterSave()
    {
        return [
            [''],
            [false]
        ];
    }

    /**
     * Test data for testBeforeSaveValueDeletion.
     *
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
     * Test data for testBeforeSaveValueInvalid.
     *
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
}
