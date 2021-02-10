<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Option\Type;

use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Catalog\Model\Product\Option\Type\File;
use Magento\Catalog\Model\Product\Option\UrlBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Quote\Model\Quote\Item\OptionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test file option type
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var WriteInterface|MockObject
     */
    protected $mediaDirectory;

    /**
     * @var Database|MockObject
     */
    protected $coreFileStorageDatabase;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var Json|MockObject
     */
    private $serializer;

    /**
     * @var UrlBuilder|MockObject
     */
    private $urlBuilder;

    /**
     * @var Escaper|MockObject
     */
    private $escaper;

    /**
     * @var OptionFactory|MockObject
     */
    private $itemOptionFactoryMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mediaDirectory = $this->getMockBuilder(WriteInterface::class)
            ->getMock();

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA, DriverPool::FILE)
            ->willReturn($this->mediaDirectory);

        $this->serializer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'unserialize'])
            ->getMock();

        $this->urlBuilder = $this->getMockBuilder(UrlBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemOptionFactoryMock = $this->getMockBuilder(OptionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->coreFileStorageDatabase = $this->createPartialMock(
            Database::class,
            ['copyFile', 'checkDbUsage']
        );

        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
    }

    /**
     * @return File
     */
    protected function getFileObject()
    {
        return $this->objectManager->getObject(
            File::class,
            [
                'filesystem' => $this->filesystemMock,
                'coreFileStorageDatabase' => $this->coreFileStorageDatabase,
                'serializer' => $this->serializer,
                'urlBuilder' => $this->urlBuilder,
                'escaper' => $this->escaper,
                'itemOptionFactory' => $this->itemOptionFactoryMock,
            ]
        );
    }

    public function testGetFormattedOptionValueWithUnserializedValue()
    {
        $fileObject = $this->getFileObject();

        $value = 'some unserialized value, 1, 2.test';
        $this->assertEquals($value, $fileObject->getFormattedOptionValue($value));
    }

    public function testGetCustomizedView()
    {
        $fileObject = $this->getFileObject();
        $optionInfo = ['option_value' => 'some serialized data'];

        $dataAfterSerialize = ['some' => 'array'];

        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with('some serialized data')
            ->willReturn($dataAfterSerialize);

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->willReturn('someUrl');

        $this->escaper->expects($this->once())
            ->method('escapeHtml')
            ->willReturn('string');

        $this->assertEquals(
            '<a href="someUrl" target="_blank">string</a> ',
            $fileObject->getCustomizedView($optionInfo)
        );
    }

    public function testCopyQuoteToOrderWithDbUsage()
    {
        $optionMock = $this->getMockBuilder(OptionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();

        $quotePath = '/quote/path/path/uploaded.file';
        $orderPath = '/order/path/path/uploaded.file';

        $quoteValue = "{\"quote_path\":\"$quotePath\",\"order_path\":\"$orderPath\"}";

        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($quoteValue)
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $optionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($quoteValue);

        $this->mediaDirectory->expects($this->once())
            ->method('isFile')
            ->with($quotePath)
            ->willReturn(true);

        $this->mediaDirectory->expects($this->once())
            ->method('isReadable')
            ->with($quotePath)
            ->willReturn(true);

        $this->mediaDirectory->expects($this->exactly(2))
            ->method('getAbsolutePath')
            ->willReturn('/file.path');

        $this->coreFileStorageDatabase->expects($this->once())
            ->method('checkDbUsage')
            ->willReturn(true);

        $this->coreFileStorageDatabase->expects($this->once())
            ->method('copyFile')
            ->willReturn('true');

        $fileObject = $this->getFileObject();
        $fileObject->setData('configuration_item_option', $optionMock);

        $this->assertInstanceOf(
            File::class,
            $fileObject->copyQuoteToOrder()
        );
    }

    public function testCopyQuoteToOrderWithoutUsage()
    {
        $optionMock = $this->getMockBuilder(OptionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();

        $quotePath = '/quote/path/path/uploaded.file';
        $orderPath = '/order/path/path/uploaded.file';

        $quoteValue = "{\"quote_path\":\"$quotePath\",\"order_path\":\"$orderPath\"}";

        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($quoteValue)
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $optionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($quoteValue);

        $this->mediaDirectory->expects($this->once())
            ->method('isFile')
            ->with($quotePath)
            ->willReturn(true);

        $this->mediaDirectory->expects($this->once())
            ->method('isReadable')
            ->with($quotePath)
            ->willReturn(true);

        $this->mediaDirectory->expects($this->never())
            ->method('getAbsolutePath')
            ->willReturn('/file.path');

        $this->coreFileStorageDatabase->expects($this->once())
            ->method('checkDbUsage')
            ->willReturn(false);

        $this->coreFileStorageDatabase->expects($this->any())
            ->method('copyFile')
            ->willReturn(false);

        $fileObject = $this->getFileObject();
        $fileObject->setData('configuration_item_option', $optionMock);

        $this->assertInstanceOf(
            File::class,
            $fileObject->copyQuoteToOrder()
        );
    }

    public function testGetFormattedOptionValue()
    {
        $resultValue = ['result'];
        $optionValue = json_encode($resultValue);
        $urlParameter = 'parameter';

        $fileObject = $this->getFileObject();
        $fileObject->setCustomOptionUrlParams($urlParameter);
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($optionValue)
            ->willReturn($resultValue);

        $resultValue['url'] = [
            'route' => 'sales/download/downloadCustomOption',
            'params' => $fileObject->getCustomOptionUrlParams()
        ];

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($resultValue)
            ->willReturn(json_encode($resultValue));

        $option = $this->getMockBuilder(Option::class)
            ->setMethods(['setValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $option->expects($this->once())
            ->method('setValue')
            ->with(json_encode($resultValue));

        $fileObject->setConfigurationItemOption($option);

        $fileObject->getFormattedOptionValue($optionValue);
    }

    public function testGetFormattedOptionValueInvalid()
    {
        $optionValue = 'invalid json option value...';
        $this->assertEquals($optionValue, $this->getFileObject()->getFormattedOptionValue($optionValue));
    }

    public function testGetEditableOptionValue()
    {
        $configurationItemOption = $this->getMockBuilder(
            OptionInterface::class
        )->disableOriginalConstructor()
            ->setMethods(['getId', 'getValue'])
            ->getMock();
        $configurationItemOption->expects($this->once())
            ->method('getId')
            ->willReturn(2);
        $fileObject = $this->getFileObject()->setData('configuration_item_option', $configurationItemOption);
        $optionTitle = 'Option Title';
        $optionValue = json_encode(['title' => $optionTitle]);
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($optionValue)
            ->willReturn(json_decode($optionValue, true));
        $this->escaper->expects($this->once())
            ->method('escapeHtml')
            ->with($optionTitle)
            ->willReturn($optionTitle);

        $this->assertEquals('Option Title [2]', $fileObject->getEditableOptionValue($optionValue));
    }

    public function testGetEditableOptionValueInvalid()
    {
        $fileObject = $this->getFileObject();
        $optionValue = '#invalid jSoN*(&@#^$(*&';
        $this->escaper->expects($this->never())
            ->method('escapeHtml');

        $this->assertEquals($optionValue, $fileObject->getEditableOptionValue($optionValue));
    }

    public function testParseOptionValue()
    {
        $optionTitle = 'Option Title';
        $optionValue = json_encode(['title' => $optionTitle]);

        $userInput = 'Option [2]';
        $fileObject = $this->getFileObject();

        $itemMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getValue'])
            ->getMock();

        $itemMock->expects($this->any())
            ->method('load')->willReturnSelf();

        $itemMock->expects($this->any())
            ->method('getValue')
            ->willReturn($optionValue);

        $this->itemOptionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemMock);

        $this->assertEquals($optionValue, $fileObject->parseOptionValue($userInput, []));
    }

    public function testParseOptionValueNoId()
    {
        $optionValue = 'value';

        $userInput = 'Option [xx]';
        $fileObject = $this->getFileObject();

        $itemMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getValue'])
            ->getMock();

        $itemMock->expects($this->any())
            ->method('load')->willReturnSelf();

        $itemMock->expects($this->any())
            ->method('getValue')
            ->willReturn($optionValue);

        $this->itemOptionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemMock);

        $this->assertNull($fileObject->parseOptionValue($userInput, []));
    }

    public function testParseOptionValueInvalid()
    {
        $optionValue = 'Invalid json serialized value...';

        $userInput = 'Option [2]';
        $fileObject = $this->getFileObject();

        $itemMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getValue'])
            ->getMock();

        $itemMock->expects($this->any())
            ->method('load')->willReturnSelf();

        $itemMock->expects($this->any())
            ->method('getValue')
            ->willReturn($optionValue);

        $this->itemOptionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemMock);

        $this->assertNull($fileObject->parseOptionValue($userInput, []));
    }

    public function testPrepareOptionValueForRequest()
    {
        $resultValue = ['result'];
        $optionValue = json_encode($resultValue);
        $fileObject = $this->getFileObject();
        $this->assertEquals($resultValue, $fileObject->prepareOptionValueForRequest($optionValue));
    }
}
