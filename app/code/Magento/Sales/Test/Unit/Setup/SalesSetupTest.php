<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Setup;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for sales setup model.
 *
 * @package Magento\Sales\Test\Unit\Setup
 */
class SalesSetupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Setup\SalesSetup
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleDataSetupMock;

    /**
     * @var \Magento\Eav\Model\Entity\Setup\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $this->moduleDataSetupMock = $this->getMockBuilder(\Magento\Framework\Setup\ModuleDataSetupInterface::class)
            ->getMockForAbstractClass();
        $this->contextMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Setup\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheMock = $this->getMockBuilder(\Magento\Framework\App\CacheInterface::class)
            ->getMockForAbstractClass();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Sales\Setup\SalesSetup::class,
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
