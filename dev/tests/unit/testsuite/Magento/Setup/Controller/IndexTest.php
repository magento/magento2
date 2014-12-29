<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Setup\Controller;

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
