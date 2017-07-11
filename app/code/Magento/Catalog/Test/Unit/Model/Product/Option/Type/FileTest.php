<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Option\Type;

use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\SerializationException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverPool;

/**
 * Class FileTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreFileStorageDatabase;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var \Magento\Catalog\Model\Product\Option\UrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaper;

    /**
     * @var \Magento\Quote\Model\Quote\Item\OptionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemOptionFactoryMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mediaDirectory = $this->getMockBuilder(WriteInterface::class)
            ->getMock();

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA, DriverPool::FILE)
            ->willReturn($this->mediaDirectory);

        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'unserialize'])
            ->getMock();

        $this->urlBuilder = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\UrlBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaper = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemOptionFactoryMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\OptionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->coreFileStorageDatabase = $this->getMock(
            \Magento\MediaStorage\Helper\File\Storage\Database::class,
            ['copyFile', 'checkDbUsage'],
            [],
            '',
            false
        );

        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_decode($value, true);
                    }
                )
            );

        $this->serializer->expects($this->any())
            ->method('serialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_encode($value);
                    }
                )
            );
    }

    /**
     * @return \Magento\Catalog\Model\Product\Option\Type\File
     */
    protected function getFileObject()
    {
        return $this->objectManager->getObject(
            \Magento\Catalog\Model\Product\Option\Type\File::class,
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
            ->will($this->returnValue($quoteValue));

        $this->mediaDirectory->expects($this->once())
            ->method('isFile')
            ->with($this->equalTo($quotePath))
            ->will($this->returnValue(true));

        $this->mediaDirectory->expects($this->once())
            ->method('isReadable')
            ->with($this->equalTo($quotePath))
            ->will($this->returnValue(true));

        $this->mediaDirectory->expects($this->exactly(2))
            ->method('getAbsolutePath')
            ->will($this->returnValue('/file.path'));

        $this->coreFileStorageDatabase->expects($this->once())
            ->method('checkDbUsage')
            ->willReturn(true);

        $this->coreFileStorageDatabase->expects($this->once())
            ->method('copyFile')
            ->will($this->returnValue('true'));

        $fileObject = $this->getFileObject();
        $fileObject->setData('configuration_item_option', $optionMock);

        $this->assertInstanceOf(
            \Magento\Catalog\Model\Product\Option\Type\File::class,
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
            ->will($this->returnValue($quoteValue));

        $this->mediaDirectory->expects($this->once())
            ->method('isFile')
            ->with($this->equalTo($quotePath))
            ->will($this->returnValue(true));

        $this->mediaDirectory->expects($this->once())
            ->method('isReadable')
            ->with($this->equalTo($quotePath))
            ->will($this->returnValue(true));

        $this->mediaDirectory->expects($this->never())
            ->method('getAbsolutePath')
            ->will($this->returnValue('/file.path'));

        $this->coreFileStorageDatabase->expects($this->once())
            ->method('checkDbUsage')
            ->willReturn(false);

        $this->coreFileStorageDatabase->expects($this->any())
            ->method('copyFile')
            ->willReturn(false);

        $fileObject = $this->getFileObject();
        $fileObject->setData('configuration_item_option', $optionMock);

        $this->assertInstanceOf(
            \Magento\Catalog\Model\Product\Option\Type\File::class,
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

        $option = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
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
            \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class
        )->disableOriginalConstructor()
            ->setMethods(['getId', 'getValue'])
            ->getMock();
        $configurationItemOption->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(2));
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
            ->will($this->returnValue($optionTitle));

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

        $itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getValue'])
            ->getMock();

        $itemMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());

        $itemMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($optionValue));

        $this->itemOptionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($itemMock));

        $this->assertEquals($optionValue, $fileObject->parseOptionValue($userInput, []));
    }

    public function testParseOptionValueNoId()
    {
        $optionValue = 'value';

        $userInput = 'Option [xx]';
        $fileObject = $this->getFileObject();

        $itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getValue'])
            ->getMock();

        $itemMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());

        $itemMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($optionValue));

        $this->itemOptionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($itemMock));

        $this->assertEquals(null, $fileObject->parseOptionValue($userInput, []));
    }

    public function testParseOptionValueInvalid()
    {
        $optionValue = 'Invalid json serialized value...';

        $userInput = 'Option [2]';
        $fileObject = $this->getFileObject();

        $itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getValue'])
            ->getMock();

        $itemMock->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());

        $itemMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($optionValue));

        $this->itemOptionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($itemMock));

        $this->assertEquals(null, $fileObject->parseOptionValue($userInput, []));
    }

    public function testPrepareOptionValueForRequest()
    {
        $resultValue = ['result'];
        $optionValue = json_encode($resultValue);
        $fileObject = $this->getFileObject();
        $this->assertEquals($resultValue, $fileObject->prepareOptionValueForRequest($optionValue));
    }
}
