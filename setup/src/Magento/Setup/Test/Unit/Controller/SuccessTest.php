<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\Success;

class SuccessTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexAction()
    {
        $sampleData = $this->getMock('Magento\Setup\Model\SampleData', ['isDeployed'], [], '', false);
        /** @var $controller Success */
        $controller = new Success($sampleData);
        $sampleData->expects($this->once())->method('isDeployed');
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }
}
