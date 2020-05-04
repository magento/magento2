<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Menu\Builder\Command;

use Magento\Backend\Model\Menu\Builder\Command\Remove;
use PHPUnit\Framework\TestCase;

class RemoveTest extends TestCase
{
    /**
     * @var Remove
     */
    protected $_model;

    protected $_params = ['id' => 'item'];

    protected function setUp(): void
    {
        $this->_model = new Remove($this->_params);
    }

    public function testExecuteMarksItemAsRemoved()
    {
        $params = $this->_model->execute([]);
        $this->_params['removed'] = true;
        $this->assertEquals($this->_params, $params);
    }
}
