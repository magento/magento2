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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Helper\Product;

/**
 * @magentoAppArea frontend
 */
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

    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\App\State')->setAreaCode('frontend');
        $objectManager->get('Magento\App\Http\Context')
            ->setValue(\Magento\Customer\Helper\Data::CONTEXT_AUTH, false, false);
        $objectManager->get('Magento\View\DesignInterface')
            ->setDefaultDesignTheme();
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Catalog\Helper\Product\View');
        $request = $objectManager->get('Magento\TestFramework\Request');
        $request->setRouteName('catalog')->setControllerName('product')->setActionName('view');
        $arguments = array(
            'request' => $request,
            'response' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\TestFramework\Response'
            )
        );
        $context = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\App\Action\Context',
            $arguments
        );
        $this->_controller = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Controller\Product',
            array('context' => $context)
        );

        $this->_layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\View\LayoutInterface'
        );
    }

    /**
     * Cleanup session, contaminated by product initialization methods
     */
    protected function tearDown()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Session'
        )->unsLastViewedProductId();
        $this->_controller = null;
        $this->_helper = null;
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testInitProductLayout()
    {
        $uniqid = uniqid();
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE)->setId(99)->setUrlKey($uniqid);
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Registry')->register('product', $product);

        $this->_helper->initProductLayout($product, $this->_controller);
        $rootBlock = $this->_layout->getBlock('root');
        $this->assertInstanceOf('Magento\Theme\Block\Html', $rootBlock);
        $this->assertContains("product-{$uniqid}", $rootBlock->getBodyClass());
        $handles = $this->_layout->getUpdate()->getHandles();
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
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Catalog\Model\Session'
            )->getLastViewedProductId()
        );
    }

    /**
     * @expectedException \Magento\Model\Exception
     * @magentoAppIsolation enabled
     */
    public function testPrepareAndRenderWrongController()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $controller = $objectManager->create('Magento\Catalog\Controller\Product');
        $this->_helper->prepareAndRender(10, $controller);
    }

    /**
     * @magentoAppIsolation enabled
     * @expectedException \Magento\Model\Exception
     */
    public function testPrepareAndRenderWrongProduct()
    {
        $this->_helper->prepareAndRender(999, $this->_controller);
    }
}
