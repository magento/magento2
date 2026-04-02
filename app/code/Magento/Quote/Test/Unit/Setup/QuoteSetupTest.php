<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Setup;

use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Setup\QuoteSetup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Quote module setup model.
 */
class QuoteSetupTest extends TestCase
{
    /**
     * @var QuoteSetup
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
        $this->moduleDataSetupMock = $this->createMock(ModuleDataSetupInterface::class);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        // Prepare ObjectManager mappings for optional EAV dependencies used inside EavSetup constructor
        $this->objectManagerHelper->prepareObjectManager([
            [\Magento\Eav\Setup\AddOptionToAttribute::class,
                $this->createMock(\Magento\Eav\Setup\AddOptionToAttribute::class)],
            [\Magento\Eav\Model\Validator\Attribute\Code::class,
                $this->createMock(\Magento\Eav\Model\Validator\Attribute\Code::class)],
            [\Magento\Eav\Model\ReservedAttributeCheckerInterface::class,
                $this->createMock(\Magento\Eav\Model\ReservedAttributeCheckerInterface::class)],
            [\Magento\Eav\Model\AttributeFactory::class,
                $this->createMock(\Magento\Eav\Model\AttributeFactory::class)],
            [\Magento\Eav\Model\Config::class,
                $this->createMock(\Magento\Eav\Model\Config::class)],
        ]);
        $this->model = $this->objectManagerHelper->getObject(
            QuoteSetup::class,
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
            ->with('checkout');
        $this->model->getConnection();
    }
}
