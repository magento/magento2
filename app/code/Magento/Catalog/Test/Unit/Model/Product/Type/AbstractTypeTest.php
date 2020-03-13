<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Type;

use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Option\Type\File\ValidatorFile;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Catalog\Model\ResourceModel\Product as ResourceModelProduct;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Helper\File\Storage\Database;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Verify Abstract Type class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractTypeTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var Simple|MockObject
     */
    protected $model;

    /**
     * @var Product|MockObject
     */
    protected $product;

    /**
     * @var ResourceModelProduct|MockObject
     */
    protected $productResource;

    /**
     * @var Attribute|MockObject
     */
    protected $attribute;

    /**
     * @var Filesystem|MockObject
     */
    protected $_filesystemMock;

    /**
     * @var WriteInterface|MockObject
     */
    protected $directoryMock;

    /**
     * @var \Zend_File_Transfer_Adapter_Http|MockObject
     */
    protected $httpMock;

    /**
     * @var ValidatorFile|MockObject
     */
    protected $validatorFileMock;

    /**
     * @var Database|MockObject
     */
    protected $_fileStorageDbMock;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->product = $this->getMockBuilder(Product::class)
            ->setMethods(['getHasOptions', '__wakeup', '__sleep', 'getResource', 'getStatus'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productResource = $this->getMockBuilder(ResourceModelProduct::class)
            ->setMethods(['getSortedAttributes', 'loadAllAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->product->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($this->productResource));
        $this->attribute = $this->getMockBuilder(Attribute::class)
            ->setMethods(['getGroupSortPath', 'getSortPath', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_filesystemMock = $this->createMock(Filesystem::class);
        $this->_fileStorageDbMock = $this->createMock(Database::class);
        $this->directoryMock = $this->createMock(WriteInterface::class);
        $this->httpMock = $this->createMock(\Zend_File_Transfer_Adapter_Http::class);
        $this->validatorFileMock = $this->getMockBuilder(ValidatorFile::class)
            ->setMethods(['setIsValid'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManager($this);
        $this->model = $this->objectManagerHelper->getObject(
            Simple::class,
            [
                '_filesystem' => $this->_filesystemMock,
            ]
        );
    }

    /**
     * Verify IsSalable
     *
     * @return void
     */
    public function testIsSalable():void
    {
        $this->product->expects($this->any())->method('getStatus')->will(
            $this->returnValue(Status::STATUS_ENABLED)
        );
        $this->product->setData('is_salable', 3);
        $this->assertEquals(true, $this->model->isSalable($this->product));
    }

    /**
     * Verify GetAttributeById
     *
     * @return void
     */
    public function testGetAttributeById():void
    {
        $this->productResource->expects($this->any())
            ->method('loadAllAttributes')
            ->will(
                $this->returnValue($this->productResource)
            );
        $this->productResource->expects($this->any())
            ->method('getSortedAttributes')
            ->will(
                $this->returnValue([$this->attribute])
            );
        $this->attribute->setId(1);

        $this->assertEquals($this->attribute, $this->model->getAttributeById(1, $this->product));
        $this->assertNull($this->model->getAttributeById(0, $this->product));
    }

    /**
     * Verify AttributesCompare
     *
     * @var int $attr1
     * @var int $attr2
     * @var int $expectedResult
     * @dataProvider attributeCompareProvider
     * @return void
     */
    public function testAttributesCompare(
        int $attr1,
        int $attr2,
        int $expectedResult
    ):void {
        $attribute = $this->attribute;
        $attribute->expects($this->any())
            ->method('getSortPath')
            ->will($this->returnValue(1));

        $attribute2 = clone $attribute;

        $attribute->expects($this->any())
            ->method('getGroupSortPath')
            ->will($this->returnValue($attr1));
        $attribute2->expects($this->any())
            ->method('getGroupSortPath')
            ->will($this->returnValue($attr2));

        $this->assertEquals($expectedResult, $this->model->attributesCompare($attribute, $attribute2));
    }

    /**
     * Data provider to testAttributesCompare
     *
     * @return array
     */
    public function attributeCompareProvider(): array
    {
        return [
            [2, 2, 0],
            [2, 1, 1],
            [1, 2, -1]
        ];
    }

    /**
     * Verify GetSetAttributes
     *
     * @return void
     */
    public function testGetSetAttributes(): void
    {
        $this->productResource->expects($this->once())
            ->method('loadAllAttributes')
            ->will(
                $this->returnValue($this->productResource)
            );
        $this->productResource->expects($this->once())
            ->method('getSortedAttributes')
            ->will($this->returnValue(5));
        $this->assertEquals(5, $this->model->getSetAttributes($this->product));
        //Call the method for a second time, the cached copy should be used
        $this->assertEquals(5, $this->model->getSetAttributes($this->product));
    }

    /**
     * Verify HasOptions
     *
     * @return void
     */
    public function testHasOptions():void
    {
        $this->product->expects($this->once())->method('getHasOptions')->will($this->returnValue(true));
        $this->assertEquals(true, $this->model->hasOptions($this->product));
    }

    /**
     * Verify processFileQueue
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testProcessFileQueue():void
    {
        $this->model->addFileQueue([
            'operation' => 'receive_uploaded_file',
            'src_name' => 'filename',
            'dst_name' => 'filename2',
            'option' => $this->validatorFileMock,
            'uploader' => $this->httpMock
        ]);

        $this->_filesystemMock->expects($this->once())
              ->method('getDirectoryWrite')
              ->with('base')
              ->willReturn($this->directoryMock);

        $this->directoryMock->expects($this->once())
              ->method('getRelativePath')
              ->with('.')
              ->willReturn('.');

        $this->directoryMock->expects($this->once())
              ->method('create')
              ->with('.')
              ->willReturnSelf();

        $this->validatorFileMock->expects($this->any())
              ->method('setIsValid')
              ->with(false);

        $this->never(
            $this->throwException(
                new \Magento\Framework\Exception\LocalizedException(
                    __('The file upload failed. Try to upload again.')
                )
            )
        );

        $this->_fileStorageDbMock->expects($this->any())
              ->method('saveFile')
              ->with('filename2')
              ->willReturnSelf();

        $this->assertEquals($this->model, $this->model->processFileQueue());
    }
}
