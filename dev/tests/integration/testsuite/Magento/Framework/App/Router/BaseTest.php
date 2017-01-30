<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Router;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Router\Base
     */
    protected $_model;

    protected function setUp()
    {
        $options = ['routerId' => 'standard'];
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Router\Base',
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
        $request->setRequestUri('framework/index/index');
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
            'Magento\Framework\Controller\Index',
            $this->_model->getActionClassName('Magento_Framework', 'index')
        );
    }
}
