<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Eav\Model\Entity\Collection;

class AbstractCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Collection\AbstractCollectionStub|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Core\Model\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreEntityFactoryMock;

    /**
     * @var \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreResourceMock;

    /**
     * @var \Magento\Eav\Model\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Magento\Eav\Model\Resource\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceHelperMock;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorFactoryMock;

    public function setUp()
    {
        $this->coreEntityFactoryMock = $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false);
        $this->loggerMock = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
        $this->fetchStrategyMock = $this->getMock(
            'Magento\Framework\Data\Collection\Db\FetchStrategyInterface',
            array(),
            array(),
            '',
            false
        );
        $this->eventManagerMock = $this->getMock(
            'Magento\Framework\Event\ManagerInterface',
            array(),
            array(),
            '',
            false
        );
        $this->configMock = $this->getMock('Magento\Eav\Model\Config', array(), array(), '', false);
        $this->coreResourceMock = $this->getMock(
            'Magento\Framework\App\Resource',
            array('getConnection'),
            array(),
            '',
            false
        );
        $this->resourceHelperMock = $this->getMock('Magento\Eav\Model\Resource\Helper', array(), array(), '', false);
        $this->validatorFactoryMock = $this->getMock(
            'Magento\Framework\Validator\UniversalFactory',
            array(),
            array(),
            '',
            false
        );
        $this->entityFactoryMock = $this->getMock('Magento\Eav\Model\EntityFactory', array(), array(), '', false);
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject */
        $connectionMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', array(), array(), '', false);
        /** @var $selectMock \Zend_Db_Select|\PHPUnit_Framework_MockObject_MockObject */
        $selectMock = $this->getMock('Zend_Db_Select', array(), array(), '', false);
        $this->coreEntityFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnCallback(array($this, 'getMagentoObject'))
        );
        $connectionMock->expects($this->any())->method('select')->will($this->returnValue($selectMock));

        $this->coreResourceMock->expects(
            $this->any()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($connectionMock)
        );
        $entityMock = $this->getMock('Magento\Eav\Model\Entity\AbstractEntity', array(), array(), '', false);
        $entityMock->expects($this->once())->method('getReadConnection')->will($this->returnValue($connectionMock));
        $entityMock->expects($this->once())->method('getDefaultAttributes')->will($this->returnValue(array()));

        $this->validatorFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'test_entity_model' // see \Magento\Eav\Model\Entity\Collection\AbstractCollectionStub
        )->will(
            $this->returnValue($entityMock)
        );

        $this->model = new \Magento\Eav\Model\Entity\Collection\AbstractCollectionStub(
            $this->coreEntityFactoryMock,
            $this->loggerMock,
            $this->fetchStrategyMock,
            $this->eventManagerMock,
            $this->configMock,
            $this->coreResourceMock,
            $this->entityFactoryMock,
            $this->resourceHelperMock,
            $this->validatorFactoryMock,
            null
        );
    }

    public function tearDown()
    {
        $this->model = null;
    }

    /**
     * @dataProvider getItemsDataProvider
     */
    public function testClear($values, $count)
    {
        $this->fetchStrategyMock->expects($this->once())->method('fetchAll')->will($this->returnValue($values));

        $testId = array_pop($values)['id'];
        $this->assertCount($count, $this->model->getItems());
        $this->assertNotNull($this->model->getItemById($testId));
        $this->model->clear();
        $this->assertNull($this->model->getItemById($testId));
    }

    /**
     * @dataProvider getItemsDataProvider
     */
    public function testRemoveAllItems($values, $count)
    {
        $this->fetchStrategyMock->expects($this->once())->method('fetchAll')->will($this->returnValue($values));

        $testId = array_pop($values)['id'];
        $this->assertCount($count, $this->model->getItems());
        $this->assertNotNull($this->model->getItemById($testId));
        $this->model->removeAllItems();
        $this->assertNull($this->model->getItemById($testId));
    }

    /**
     * @dataProvider getItemsDataProvider
     */
    public function testRemoveItemByKey($values, $count)
    {
        $this->fetchStrategyMock->expects($this->once())->method('fetchAll')->will($this->returnValue($values));

        $testId = array_pop($values)['id'];
        $this->assertCount($count, $this->model->getItems());
        $this->assertNotNull($this->model->getItemById($testId));
        $this->model->removeItemByKey($testId);
        $this->assertCount($count - 1, $this->model->getItems());
        $this->assertNull($this->model->getItemById($testId));
    }

    public function getItemsDataProvider()
    {
        return array(
            array('values' => array(array('id' => 1)), 'count' => 1),
            array('values' => array(array('id' => 1), array('id' => 2)), 'count' => 2),
            array('values' => array(array('id' => 2), array('id' => 3)), 'count' => 2)
        );
    }

    public function getMagentoObject()
    {
        return new \Magento\Framework\Object();
    }
}
