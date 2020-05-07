<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Type;

use Magento\Catalog\Model\Product\Type\Virtual;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Helper\File\Storage\Database;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class VirtualTest extends TestCase
{
    /**
     * @var Virtual
     */
    protected $_model;

    protected function setUp(): void
    {
        $objectHelper = new ObjectManager($this);
        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $coreRegistryMock = $this->createMock(Registry::class);
        $fileStorageDbMock = $this->createMock(Database::class);
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $productFactoryMock = $this->createMock(ProductFactory::class);
        $this->_model = $objectHelper->getObject(
            Virtual::class,
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
