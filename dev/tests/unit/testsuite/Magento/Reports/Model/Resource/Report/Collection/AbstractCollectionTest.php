<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Reports\Model\Resource\Report\Collection;

class AbstractCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractCollection
     */
    protected $_model;

    protected function setUp()
    {
        $entityFactory = $this->getMock('\Magento\Core\Model\EntityFactory', [], [], '', false);
        $logger = $this->getMock('\Magento\Framework\Logger', [], [], '', false);
        $fetchStrategy = $this->getMock('\Magento\Framework\Data\Collection\Db\FetchStrategy\Query', [], [], '', false);
        $eventManager = $this->getMock('\Magento\Framework\Event\Manager', [], [], '', false);
        $connection = $this->getMock('\Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);

        $resource = $this->getMockBuilder('\Magento\Framework\Model\Resource\Db\AbstractDb')
            ->disableOriginalConstructor()
            ->setMethods(['getReadConnection'])
            ->getMockForAbstractClass();
        $resource->method('getReadConnection')->willReturn($connection);

        $this->_model = new AbstractCollection(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    public function testIsSubtotalsGetDefault()
    {
        $this->assertFalse($this->_model->isSubTotals());
    }

    public function testSetIsSubtotals()
    {
        $this->_model->setIsSubTotals(true);
        $this->assertTrue($this->_model->isSubTotals());

        $this->_model->setIsSubTotals(false);
        $this->assertFalse($this->_model->isSubTotals());
    }
}