<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu\Builder\Command;

class UpdateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu\Builder\Command\Update
     */
    protected $_model;

    protected $_params = ['id' => 'item', 'title' => 'item', 'module' => 'Magento_Backend', 'parent' => 'parent'];

    protected function setUp()
    {
        $this->_model = new \Magento\Backend\Model\Menu\Builder\Command\Update($this->_params);
    }

    public function testExecuteFillsEmptyItemWithData()
    {
        $params = $this->_model->execute([]);
        $this->assertEquals($this->_params, $params);
    }

    public function testExecuteRewritesDataInFilledItem()
    {
        $params = $this->_model->execute(['title' => 'newitem']);
        $this->assertEquals($this->_params, $params);
    }
}
