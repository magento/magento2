<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Menu\Builder\Command;

class AddTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu\Builder\Command\Add
     */
    protected $_model;

    protected $_params = [
        'id' => 'item',
        'title' => 'item',
        'module' => 'Magento_Backend',
        'parent' => 'parent',
        'resource' => 'Magento_Backend::item',
    ];

    protected function setUp()
    {
        $this->_model = new \Magento\Backend\Model\Menu\Builder\Command\Add($this->_params);
    }

    public function testExecuteFillsEmptyItemWithData()
    {
        $params = $this->_model->execute([]);
        $this->assertEquals($this->_params, $params);
    }

    public function testExecuteDoesntRewriteDataInFilledItem()
    {
        $params = $this->_model->execute(['title' => 'newitem']);
        $this->_params['title'] = 'newitem';
        $this->assertEquals($this->_params, $params);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testChainWithAnotherAddCommandTrowsException()
    {
        $this->_model->chain(new \Magento\Backend\Model\Menu\Builder\Command\Add($this->_params));
    }
}
