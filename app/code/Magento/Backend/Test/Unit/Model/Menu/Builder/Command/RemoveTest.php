<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Menu\Builder\Command;

class RemoveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu\Builder\Command\Remove
     */
    protected $_model;

    protected $_params = ['id' => 'item'];

    protected function setUp()
    {
        $this->_model = new \Magento\Backend\Model\Menu\Builder\Command\Remove($this->_params);
    }

    public function testExecuteMarksItemAsRemoved()
    {
        $params = $this->_model->execute([]);
        $this->_params['removed'] = true;
        $this->assertEquals($this->_params, $params);
    }
}
