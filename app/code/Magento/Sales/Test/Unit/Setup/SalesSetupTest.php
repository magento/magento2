<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Setup;

use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Setup\SalesSetup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for sales setup model.
 *
 */
class SalesSetupTest extends TestCase
{
    /**
     * @var SalesSetup
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ModuleDataSetupInterface|MockObject
     */
    private $moduleDataSetupMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    protected function setUp(): void
    {
        $this->moduleDataSetupMock = $this->getMockBuilder(ModuleDataSetupInterface::class)
            ->getMockForAbstractClass();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheMock = $this->getMockBuilder(CacheInterface::class)
            ->getMockForAbstractClass();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            SalesSetup::class,
            [
                'setup' => $this->moduleDataSetupMock,
                'context' => $this->contextMock,
                'cache' => $this->cacheMock,
                'attrGroupCollectionFactory' => $this->collectionFactoryMock,
                'config' => $this->scopeConfigMock
            ]
        );
    }

    public function testGetConnection()
    {
        $this->moduleDataSetupMock->expects($this->once())
            ->method('getConnection')
            ->with('sales');
        $this->model->getConnection();
    }
}
