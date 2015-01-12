<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Resource\Db\Collection;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
     */
    protected $_model = null;

    protected function setUp()
    {
        $resourceModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\App\Resource');
        $resource = $this->getMockForAbstractClass(
            'Magento\Framework\Model\Resource\Db\AbstractDb',
            [$resourceModel],
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

        $fetchStrategy = $this->getMockForAbstractClass('Magento\Framework\Data\Collection\Db\FetchStrategyInterface');

        $eventManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Event\ManagerInterface'
        );

        $entityFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Core\Model\EntityFactory'
        );
        $logger = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Psr\Log\LoggerInterface');

        $this->_model = $this->getMockForAbstractClass(
            'Magento\Framework\Model\Resource\Db\Collection\AbstractCollection',
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
