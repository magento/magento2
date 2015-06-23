<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\CompleteBackup;

class CompleteBackupTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexAction()
    {
        /** @var $controller CompleteBackup */
        $controller = new CompleteBackup();
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}
