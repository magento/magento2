<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\CreateBackup;

class CreateBackupTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexAction()
    {
        /** @var $controller CreateBackup */
        $controller = new CreateBackup();
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}
