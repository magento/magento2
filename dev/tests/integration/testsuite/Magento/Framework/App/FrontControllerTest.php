<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $this->_model = $this->_objectManager->create(\Magento\Framework\App\FrontController::class);
    }

    public function testDispatch()
    {
        if (!\Magento\TestFramework\Helper\Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Can\'t test dispatch process without sending headers');
        }
        $_SERVER['HTTP_HOST'] = 'localhost';
        $this->_objectManager->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');
        $request = $this->_objectManager->get(\Magento\Framework\App\Request\Http::class);
        /* empty action */
        $request->setRequestUri('core/index/index');
        $this->assertInstanceOf(
            \Magento\Framework\Controller\ResultInterface::class,
            $this->_model->dispatch($request)
        );
    }
}
