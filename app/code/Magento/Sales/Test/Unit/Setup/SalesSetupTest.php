<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Setup;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SalesSetupTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Sales\Setup\SalesSetup */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Setup\ModuleDataSetupInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $moduleDataSetupMock;

    /** @var \Magento\Eav\Model\Entity\Setup\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cacheMock;

    /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $collectionFactoryMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfigMock;

    protected function setUp()
    {
        $this->moduleDataSetupMock = $this->getMockBuilder(\Magento\Framework\Setup\ModuleDataSetupInterface::class)
            ->getMockForAbstractClass();
        $this->contextMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Setup\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheMock = $this->getMockBuilder(\Magento\Framework\App\CacheInterface::class)
            ->getMockForAbstractClass();
        $this->collectionFactoryMock = $this->getMockBuilder(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory::class)
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
