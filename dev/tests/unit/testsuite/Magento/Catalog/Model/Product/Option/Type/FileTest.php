<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Option\Type;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rootDirectory;

    /**
     * @var \Magento\Core\Helper\File\Storage\Database|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreFileStorageDatabase;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->rootDirectory = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isFile', 'isReadable', 'getAbsolutePath'])
            ->getMockForAbstractClass();

        $this->coreFileStorageDatabase = $this->getMock(
            'Magento\Core\Helper\File\Storage\Database',
            ['copyFile'],
            [],
            '',
            false
        );
    }

    /**
     * @return \Magento\Catalog\Model\Product\Option\Type\File
     */
    protected function getFileObject()
    {
        return $this->objectManager->getObject(
            'Magento\Catalog\Model\Product\Option\Type\File',
            [
                'saleableItem' => $this->rootDirectory,
                'priceCurrency' => $this->coreFileStorageDatabase
            ]
        );
    }

    public function testCopyQuoteToOrder()
    {
        $optionMock = $this->getMockBuilder(
            'Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface'
        )->disableOriginalConstructor()->setMethods(['getValue'])->getMockForAbstractClass();

        $quotePath = '/quote/path/path/uploaded.file';
        $orderPath = '/order/path/path/uploaded.file';

        $optionMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(['quote_path' => $quotePath, 'order_path' => $orderPath]));

        $this->rootDirectory->expects($this->any())
            ->method('isFile')
            ->with($this->equalTo($quotePath))
            ->will($this->returnValue(true));

        $this->rootDirectory->expects($this->any())
            ->method('isReadable')
            ->with($this->equalTo($quotePath))
            ->will($this->returnValue(true));

        $this->rootDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnValue('/file.path'));

        $this->coreFileStorageDatabase->expects($this->any())
            ->method('copyFile')
            ->will($this->returnValue('true'));

        $fileObject = $this->getFileObject();
        $fileObject->setData('configuration_item_option', $optionMock);

        $this->assertInstanceOf(
            'Magento\Catalog\Model\Product\Option\Type\File',
            $fileObject->copyQuoteToOrder()
        );
    }
}
