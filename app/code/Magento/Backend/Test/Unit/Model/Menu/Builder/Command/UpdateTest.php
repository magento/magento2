<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Menu\Builder\Command;

use Magento\Backend\Model\Menu\Builder\Command\Update;
use PHPUnit\Framework\TestCase;

class UpdateTest extends TestCase
{
    /**
     * @var Update
     */
    protected $_model;

    protected $_params = ['id' => 'item', 'title' => 'item', 'module' => 'Magento_Backend', 'parent' => 'parent'];

    protected function setUp(): void
    {
        $this->_model = new Update($this->_params);
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
