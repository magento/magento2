<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper\Product;

use Magento\Customer\Model\Context;

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
     * @var \Magento\Framework\View\Result\Page
     */
    protected $page;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
        $this->objectManager->get('Magento\Framework\App\Http\Context')
            ->setValue(Context::CONTEXT_AUTH, false, false);
        $this->objectManager->get('Magento\Framework\View\DesignInterface')
            ->setDefaultDesignTheme();
        $this->_helper = $this->objectManager->get('Magento\Catalog\Helper\Product\View');
        $request = $this->objectManager->get('Magento\TestFramework\Request');
        $request->setRouteName('catalog')->setControllerName('product')->setActionName('view');
        $arguments = [
            'request' => $request,
            'response' => $this->objectManager->get('Magento\TestFramework\Response'),
        ];
        $context = $this->objectManager->create('Magento\Framework\App\Action\Context', $arguments);
        $this->_controller = $this->objectManager->create(
            'Magento\Catalog\Helper\Product\Stub\ProductControllerStub',
            ['context' => $context]
        );
        $resultPageFactory = $this->objectManager->get('Magento\Framework\View\Result\PageFactory');
        $this->page = $resultPageFactory->create();
    }

    /**
     * Cleanup session, contaminated by product initialization methods
     */
    protected function tearDown()
    {
        $this->objectManager->get('Magento\Catalog\Model\Session')->unsLastViewedProductId();
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
        $product = $this->objectManager->create(
            'Magento\Catalog\Model\Product'
        );
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE)->setId(99)->setUrlKey($uniqid);
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = $this->objectManager;
        $objectManager->get('Magento\Framework\Registry')->register('product', $product);

        $this->_helper->initProductLayout($this->page, $product);

        /** @var \Magento\Framework\View\Page\Config $pageConfig */
        $pageConfig = $this->objectManager->get('Magento\Framework\View\Page\Config');
        $bodyClass = $pageConfig->getElementAttribute(
            \Magento\Framework\View\Page\Config::ELEMENT_TYPE_BODY,
            \Magento\Framework\View\Page\Config::BODY_ATTRIBUTE_CLASS
        );
        $this->assertContains("product-{$uniqid}", $bodyClass);
        $handles = $this->page->getLayout()->getUpdate()->getHandles();
        $this->assertContains('catalog_product_view_type_simple', $handles);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testPrepareAndRender()
    {
        // need for \Magento\Review\Block\Form::getProductInfo()
        $this->objectManager->get('Magento\Framework\App\RequestInterface')->setParam('id', 10);

        $this->_helper->prepareAndRender($this->page, 10, $this->_controller);
        /** @var \Magento\TestFramework\Response $response */
        $response = $this->objectManager->get('Magento\TestFramework\Response');
        $this->page->renderResult($response);
        $this->assertNotEmpty($response->getBody());
        $this->assertEquals(
            10,
            $this->objectManager->get(
                'Magento\Catalog\Model\Session'
            )->getLastViewedProductId()
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @magentoAppIsolation enabled
     */
    public function testPrepareAndRenderWrongController()
    {
        $objectManager = $this->objectManager;
        $controller = $objectManager->create('Magento\Catalog\Helper\Product\Stub\ProductControllerStub');
        $this->_helper->prepareAndRender($this->page, 10, $controller);
    }

    /**
     * @magentoAppIsolation enabled
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testPrepareAndRenderWrongProduct()
    {
        $this->_helper->prepareAndRender($this->page, 999, $this->_controller);
    }
}
