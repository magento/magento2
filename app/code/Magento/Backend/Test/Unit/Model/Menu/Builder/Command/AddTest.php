<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Menu\Builder\Command;

use Magento\Backend\Model\Menu\Builder\Command\Add;
use PHPUnit\Framework\TestCase;

class AddTest extends TestCase
{
    /**
     * @var Add
     */
    protected $_model;

    /**
     * @var array
     */
    protected $_params = [
        'id' => 'item',
        'title' => 'item',
        'module' => 'Magento_Backend',
        'parent' => 'parent',
        'resource' => 'Magento_Backend::item',
    ];

    protected function setUp(): void
    {
        $this->_model = new Add($this->_params);
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

    public function testChainWithAnotherAddCommandTrowsException()
    {
        $this->expectException('InvalidArgumentException');
        $this->_model->chain(new Add($this->_params));
    }
}
