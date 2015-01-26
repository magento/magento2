<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Acl\Db;

require_once realpath(__DIR__ . '/../../../../../../../../') . '/tools/Magento/Tools/Migration/Acl/Db/Writer.php';
class WriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Migration\Acl\Db\Writer
     */
    protected $_model;

    /**
     * DB adapter
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_adapterMock;

    protected function setUp()
    {
        $this->_adapterMock = $this->getMockForAbstractClass(
            'Zend_Db_Adapter_Abstract',
            [],
            '',
            false,
            false,
            false,
            ['update']
        );
        $this->_model = new \Magento\Tools\Migration\Acl\Db\Writer($this->_adapterMock, 'dummy');
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_adapterMock);
    }

    public function testUpdate()
    {
        $this->_adapterMock->expects(
            $this->once()
        )->method(
            'update'
        )->with(
            'dummy',
            ['resource_id' => 'new'],
            ['resource_id = ?' => 'old']
        );
        $this->_model->update('old', 'new');
    }
}
