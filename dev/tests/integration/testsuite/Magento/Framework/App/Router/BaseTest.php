<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Router;

class BaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Router\Base
     */
    protected $_model;

    protected function setUp()
    {
        $options = ['routerId' => 'standard'];
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\Router\Base::class,
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
        $request = $objectManager->get(\Magento\TestFramework\Request::class);

        $this->assertInstanceOf(\Magento\Framework\App\ActionInterface::class, $this->_model->match($request));
        $request->setRequestUri('framework/index/index');
        $this->assertInstanceOf(\Magento\Framework\App\ActionInterface::class, $this->_model->match($request));

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
            \Magento\Framework\Controller\Index::class,
            $this->_model->getActionClassName('Magento_Framework', 'index')
        );
    }
}
