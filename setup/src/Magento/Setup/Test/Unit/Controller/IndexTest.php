<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Index;

class IndexTest extends \PHPUnit\Framework\TestCase
{
    public function testIndexAction()
    {
        /** @var $controller Index */
        $controller = new Index();
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $viewModel);
        $this->assertFalse($viewModel->terminate());
    }
}
