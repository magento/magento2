<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Product;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Downloadable\Model\Product\Type
     */
    protected $_model;

    protected function setUp()
    {
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $downloadableFile = $this->getMockBuilder(
            'Magento\Downloadable\Helper\File'
        )->disableOriginalConstructor()->getMock();
        $coreData = $this->getMockBuilder('Magento\Core\Helper\Data')->disableOriginalConstructor()->getMock();
        $fileStorageDb = $this->getMockBuilder(
            'Magento\Core\Helper\File\Storage\Database'
        )->disableOriginalConstructor()->getMock();
        $filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $coreRegistry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $productFactoryMock = $this->getMock('Magento\Catalog\Model\ProductFactory', [], [], '', false);
        $sampleResFactory = $this->getMock('Magento\Downloadable\Model\Resource\SampleFactory', [], [], '', false);
        $linkResource = $this->getMock('Magento\Downloadable\Model\Resource\Link', [], [], '', false);
        $linksFactory = $this->getMock('Magento\Downloadable\Model\Resource\Link\CollectionFactory', [], [], '', false);
        $samplesFactory = $this->getMock(
            'Magento\Downloadable\Model\Resource\Sample\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $sampleFactory = $this->getMock('Magento\Downloadable\Model\SampleFactory', [], [], '', false);
        $linkFactory = $this->getMock('Magento\Downloadable\Model\LinkFactory', [], [], '', false);

        $entityTypeMock = $this->getMock('Magento\Eav\Model\Entity\Type', [], [], '', false);
        $resourceProductMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Product',
            ['getEntityType'],
            [],
            '',
            false
        );
        $resourceProductMock->expects($this->any())->method('getEntityType')->will($this->returnValue($entityTypeMock));

        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            [
                'getResource',
                'canAffectOptions',
                'getLinksPurchasedSeparately',
                'setTypeHasRequiredOptions',
                'setRequiredOptions',
                'getDownloadableData',
                'setTypeHasOptions',
                'setLinksExist',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $productMock->expects($this->any())->method('getResource')->will($this->returnValue($resourceProductMock));
        $productMock->expects($this->any())->method('setTypeHasRequiredOptions')->with($this->equalTo(true))->will(
            $this->returnSelf()
        );
        $productMock->expects($this->any())->method('setRequiredOptions')->with($this->equalTo(true))->will(
            $this->returnSelf()
        );
        $productMock->expects($this->any())->method('getDownloadableData')->will($this->returnValue([]));
        $productMock->expects($this->any())->method('setTypeHasOptions')->with($this->equalTo(false));
        $productMock->expects($this->any())->method('setLinksExist')->with($this->equalTo(false));
        $productMock->expects($this->any())->method('canAffectOptions')->with($this->equalTo(true));
        $productMock->expects($this->any())->method('getLinksPurchasedSeparately')->will($this->returnValue(true));
        $productMock->expects($this->any())->method('getLinksPurchasedSeparately')->will($this->returnValue(true));
        $this->_productMock = $productMock;

        $eavConfigMock = $this->getMock('\Magento\Eav\Model\Config', ['getEntityAttributeCodes'], [], '', false);
        $eavConfigMock->expects($this->any())
            ->method('getEntityAttributeCodes')
            ->with($this->equalTo($entityTypeMock), $this->equalTo($productMock))
            ->will($this->returnValue([]));
        $this->_model = $objectHelper->getObject(
            'Magento\Downloadable\Model\Product\Type',
            [
                'eventManager' => $eventManager,
                'downloadableFile' => $downloadableFile,
                'coreData' => $coreData,
                'fileStorageDb' => $fileStorageDb,
                'filesystem' => $filesystem,
                'coreRegistry' => $coreRegistry,
                'logger' => $logger,
                'productFactory' => $productFactoryMock,
                'sampleResFactory' => $sampleResFactory,
                'linkResource' => $linkResource,
                'linksFactory' => $linksFactory,
                'samplesFactory' => $samplesFactory,
                'sampleFactory' => $sampleFactory,
                'linkFactory' => $linkFactory,
                'eavConfig' => $eavConfigMock
            ]
        );
    }

    public function testHasWeightFalse()
    {
        $this->assertFalse($this->_model->hasWeight(), 'This product has weight, but it should not');
    }

    public function testBeforeSave()
    {
        $this->_model->beforeSave($this->_productMock);
    }
}
