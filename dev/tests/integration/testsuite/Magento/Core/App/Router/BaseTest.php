<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\App\Router;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\App\Router\Base
     */
    protected $_model;

    protected function setUp()
    {
        $options = ['routerId' => 'standard'];
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Core\App\Router\Base',
            $options
        );
    }

    /**
     * @magentoAppArea frontend
     */
    public function testMatch()
    {
        if (!\Magento\TestFramework\Helper\Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Can\'t test get match without sending headers');
        }

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $request \Magento\TestFramework\Request */
        $request = $objectManager->get('Magento\TestFramework\Request');

        $this->assertInstanceOf('Magento\Framework\App\ActionInterface', $this->_model->match($request));
        $request->setRequestUri('core/index/index');
        $this->assertInstanceOf('Magento\Framework\App\ActionInterface', $this->_model->match($request));

        $request->setPathInfo(
            'not_exists/not_exists/not_exists'
        )->setModuleName(
            'not_exists'
        )->setControllerName(
            'not_exists'
        )->setActionName(
            'not_exists'
        );
        $this->assertNull($this->_model->match($request));
    }

    public function testGetControllerClassName()
    {
        $this->assertEquals(
            'Magento\Core\Controller\Index',
            $this->_model->getActionClassName('Magento_Core', 'index')
        );
    }
}
