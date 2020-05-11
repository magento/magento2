<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Type;

use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Helper\File\Storage\Database;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SimpleTest extends TestCase
{
    /**
     * @var Simple
     */
    protected $_model;

    protected function setUp(): void
    {
        $objectHelper = new ObjectManager($this);
        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $fileStorageDbMock = $this->createMock(Database::class);
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $coreRegistry = $this->createMock(Registry::class);
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $productFactoryMock = $this->createMock(ProductFactory::class);
        $this->_model = $objectHelper->getObject(
            Simple::class,
            [
                'productFactory' => $productFactoryMock,
                'eventManager' => $eventManager,
                'fileStorageDb' => $fileStorageDbMock,
                'filesystem' => $filesystem,
                'coreRegistry' => $coreRegistry,
                'logger' => $logger
            ]
        );
    }

    public function testHasWeightTrue()
    {
        $this->assertTrue($this->_model->hasWeight(), 'This product has not weight, but it should');
    }
}
