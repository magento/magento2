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

namespace Magento\Core\Model;

class AbstractModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\AbstractModel
     */
    protected $model;

    /**
     * @var \Magento\Core\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Core\Model\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Core\Model\Resource\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Data\Collection\Db|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceCollectionMock;

    /**
     * @var \Magento\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;

    protected function setUp()
    {

        $this->contextMock = new \Magento\Model\Context(
            $this->getMock('Magento\Logger', array(), array(), '', false),
            $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false),
            $this->getMock('Magento\App\CacheInterface', array(), array(), '', false),
            $this->getMock('Magento\App\State', array(), array(), '', false)
        );
        $this->registryMock = $this->getMock('Magento\Registry', array(), array(), '', false);
        $this->resourceMock = $this->getMock(
            'Magento\Core\Model\Resource\Db\AbstractDb',
            array(
                '_construct',
                '_getReadAdapter',
                '_getWriteAdapter',
                '__wakeup',
                'commit',
                'delete',
                'getIdFieldName',
                'rollBack'
            ),
            array(),
            '',
            false
        );
        $this->resourceCollectionMock = $this->getMock('Magento\Data\Collection\Db', array(), array(), '', false);
        $this->model = $this->getMockForAbstractClass(
            'Magento\Core\Model\AbstractModel',
            array($this->contextMock, $this->registryMock, $this->resourceMock, $this->resourceCollectionMock)
        );
        $this->adapterMock = $this->getMock('Magento\DB\Adapter\AdapterInterface', array(), array(), '', false);
        $this->resourceMock->expects($this->any())
            ->method('_getWriteAdapter')
            ->will($this->returnValue($this->adapterMock));
        $this->resourceMock->expects($this->any())
            ->method('_getReadAdapter')
            ->will($this->returnValue($this->adapterMock));
    }

    public function testDelete()
    {
        $this->adapterMock->expects($this->once())
            ->method('beginTransaction');
        $this->resourceMock->expects($this->once())
            ->method('delete');
        $this->resourceMock->expects($this->once())
            ->method('commit');
        $this->model->delete();
        $this->assertTrue($this->model->isDeleted());
    }

    /**
     * @expectedException \Exception
     */
    public function testDeleteRaiseException()
    {
        $this->adapterMock->expects($this->once())
            ->method('beginTransaction');
        $this->resourceMock->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Exception));
        $this->resourceMock->expects($this->never())
            ->method('commit');
        $this->resourceMock->expects($this->once())
            ->method('rollBack');
        $this->model->delete();
    }
}
 