<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @magentoAppArea frontend
 */
class FrontControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\FrontController
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->_objectManager->create('Magento\Framework\App\FrontController');
    }

    public function testDispatch()
    {
        if (!\Magento\TestFramework\Helper\Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Cant\'t test dispatch process without sending headers');
        }
        $_SERVER['HTTP_HOST'] = 'localhost';
        $this->_objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
        $request = $this->_objectManager->create('Magento\Framework\App\Request\Http');
        /* empty action */
        $request->setRequestUri('core/index/index');
        $this->assertEmpty($this->_model->dispatch($request)->getBody());
    }
}
