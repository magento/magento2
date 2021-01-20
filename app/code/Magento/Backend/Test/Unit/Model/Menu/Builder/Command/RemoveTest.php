<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Menu\Builder\Command;

class RemoveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu\Builder\Command\Remove
     */
    protected $_model;

    protected $_params = ['id' => 'item'];

    protected function setUp(): void
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
