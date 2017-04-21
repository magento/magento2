<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db\Collection;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $_model = null;

    protected function setUp()
    {
        $resourceModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\App\ResourceConnection::class);
        $context = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Model\ResourceModel\Db\Context::class,
            ['resource' => $resourceModel]
        );

        $resource = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [$context],
            '',
            true,
            true,
            true,
            ['getMainTable', 'getIdFieldName']
        );

        $resource->expects(
            $this->any()
        )->method(
            'getMainTable'
        )->will(
            $this->returnValue($resource->getTable('store_website'))
        );
        $resource->expects($this->any())->method('getIdFieldName')->will($this->returnValue('website_id'));

        $fetchStrategy = $this->getMockForAbstractClass(
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class
        );

        $eventManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Event\ManagerInterface::class
        );

        $entityFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Data\Collection\EntityFactory::class
        );
        $logger = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Psr\Log\LoggerInterface::class);

        $this->_model = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class,
            [$entityFactory, $logger, $fetchStrategy, $eventManager, null, $resource]
        );
    }

    public function testGetAllIds()
    {
        $allIds = $this->_model->getAllIds();
        sort($allIds);
        $this->assertEquals(['0', '1'], $allIds);
    }

    public function testGetAllIdsWithBind()
    {
        $this->_model->getSelect()->where('code = :code');
        $this->_model->addBindParam('code', 'admin');
        $this->assertEquals(['0'], $this->_model->getAllIds());
    }
}
