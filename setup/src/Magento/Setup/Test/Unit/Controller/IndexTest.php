<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Index;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexAction()
    {
        /** @var $controller Index */
        $controller = new Index();
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertFalse($viewModel->terminate());
    }
}
