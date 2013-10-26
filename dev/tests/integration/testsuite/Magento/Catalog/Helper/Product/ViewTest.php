<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Helper\Product;

require \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\Dir')->getDir()
    . '/app/code/Magento/Catalog/Controller/Product.php';

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product\View
     */
    protected $_helper;

    /**
     * @var \Magento\Catalog\Controller\Product
     */
    protected $_controller;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\View\DesignInterface')
            ->setDefaultDesignTheme();
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Catalog\Helper\Product\View');
        $request = $objectManager->create('Magento\TestFramework\Request');
        $request->setRouteName('catalog')
            ->setControllerName('product')
            ->setActionName('view');
        $arguments = array(
            'request' => $request,
            'response' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get('Magento\TestFramework\Response'),
        );
        $context = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Controller\Varien\Action\Context', $arguments);
        $this->_controller = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Controller\Product',
            array(
                'context'  => $context,
            )
        );
    }

    /**
     * Cleanup session, contaminated by product initialization methods
     */
    protected function tearDown()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Catalog\Model\Session')
            ->unsLastViewedProductId();
        $this->_controller = null;
        $this->_helper = null;
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testInitProductLayout()
    {
        $uniqid = uniqid();
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Product');
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE)->setId(99)->setUrlKey($uniqid);
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Core\Model\Registry')->register('product', $product);

        $this->_helper->initProductLayout($product, $this->_controller);
        $rootBlock = $this->_controller->getLayout()->getBlock('root');
        $this->assertInstanceOf('Magento\Page\Block\Html', $rootBlock);
        $this->assertContains("product-{$uniqid}", $rootBlock->getBodyClass());
        $handles = $this->_controller->getLayout()->getUpdate()->getHandles();
        $this->assertContains('catalog_product_view_type_simple', $handles);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testPrepareAndRender()
    {
        $this->_helper->prepareAndRender(10, $this->_controller);
        $this->assertNotEmpty($this->_controller->getResponse()->getBody());
        $this->assertEquals(
            10,
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Catalog\Model\Session')
                ->getLastViewedProductId()
        );
    }

    /**
     * @expectedException \Magento\Core\Exception
     * @magentoAppIsolation enabled
     */
    public function testPrepareAndRenderWrongController()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $controller = $objectManager->create(
            'Magento\Core\Controller\Front\Action',
            array(
                'request'  => $objectManager->get('Magento\TestFramework\Request'),
                'response' => $objectManager->get('Magento\TestFramework\Response'),
            )
        );
        $this->_helper->prepareAndRender(10, $controller);
    }

    /**
     * @magentoAppIsolation enabled
     * @expectedException \Magento\Core\Exception
     */
    public function testPrepareAndRenderWrongProduct()
    {
        $this->_helper->prepareAndRender(999, $this->_controller);
    }

    /**
     * Test for _getSessionMessageModels
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     * @covers \Magento\Catalog\Helper\Product\View::_getSessionMessageModels
     * @magentoAppArea frontend
     */
    public function testGetSessionMessageModels()
    {
        $expectedMessages = array(
            'Magento\Catalog\Model\Session'  => 'catalog message',
            'Magento\Checkout\Model\Session' => 'checkout message',
        );

        // add messages
        foreach ($expectedMessages as $sessionModel => $messageText) {
            /** @var $session \Magento\Core\Model\Session\AbstractSession */
            $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get($sessionModel);
            $session->addNotice($messageText);
        }

        // _getSessionMessageModels invokes inside prepareAndRender
        $this->_helper->prepareAndRender(10, $this->_controller);

        // assert messages
        $actualMessages = $this->_controller->getLayout()
            ->getMessagesBlock()
            ->getMessages();
        $this->assertSameSize($expectedMessages, $actualMessages);

        sort($expectedMessages);

        /** @var $message \Magento\Core\Model\Message\Notice */
        foreach ($actualMessages as $key => $message) {
            $actualMessages[$key] = $message->getText();
        }
        sort($actualMessages);

        $this->assertEquals($expectedMessages, $actualMessages);
    }
}
