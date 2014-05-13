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
