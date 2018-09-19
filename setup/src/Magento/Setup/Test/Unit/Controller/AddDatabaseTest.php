<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\AddDatabase;

class AddDatabaseTest extends \PHPUnit\Framework\TestCase
{
    public function testIndexAction()
    {
        /** @var $controller AddDatabase */
        $controller = new AddDatabase();
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}
