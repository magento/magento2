<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Type;

class VirtualTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Type\Virtual
     */
    protected $_model;

    protected function setUp()
    {
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $eventManager = $this->getMock(\Magento\Framework\Event\ManagerInterface::class, [], [], '', false);
        $coreRegistryMock = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $fileStorageDbMock = $this->getMock(
            \Magento\MediaStorage\Helper\File\Storage\Database::class,
            [],
            [],
            '',
            false
        );
        $filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger = $this->getMock(\Psr\Log\LoggerInterface::class);
        $productFactoryMock = $this->getMock(\Magento\Catalog\Model\ProductFactory::class, [], [], '', false);
        $this->_model = $objectHelper->getObject(
            \Magento\Catalog\Model\Product\Type\Virtual::class,
            [
                'eventManager' => $eventManager,
                'fileStorageDb' => $fileStorageDbMock,
                'filesystem' => $filesystem,
                'coreRegistry' => $coreRegistryMock,
                'logger' => $logger,
                'productFactory' => $productFactoryMock
            ]
        );
    }

    public function testHasWeightFalse()
    {
        $this->assertFalse($this->_model->hasWeight(), 'This product has weight, but it should not');
    }
}
