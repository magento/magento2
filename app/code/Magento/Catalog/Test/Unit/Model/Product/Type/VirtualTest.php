<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Type;

class VirtualTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Type\Virtual
     */
    protected $_model;

    protected function setUp()
    {
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $coreRegistryMock = $this->createMock(\Magento\Framework\Registry::class);
        $fileStorageDbMock = $this->createMock(\Magento\MediaStorage\Helper\File\Storage\Database::class);
        $filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $productFactoryMock = $this->createMock(\Magento\Catalog\Model\ProductFactory::class);
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
