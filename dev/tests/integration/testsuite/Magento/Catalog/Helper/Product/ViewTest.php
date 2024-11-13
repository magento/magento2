<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper\Product;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Model\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;

/**
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends \PHPUnit\Framework\TestCase
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

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->objectManager->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');
        $this->objectManager->get(\Magento\Framework\App\Http\Context::class)
            ->setValue(Context::CONTEXT_AUTH, false, false);
        $this->objectManager->get(\Magento\Framework\View\DesignInterface::class)
            ->setDefaultDesignTheme();
        $this->_helper = $this->objectManager->get(\Magento\Catalog\Helper\Product\View::class);
        $request = $this->objectManager->get(\Magento\TestFramework\Request::class);
        $request->setRouteName('catalog')->setControllerName('product')->setActionName('view');
        $arguments = [
            'request' => $request,
            'response' => $this->objectManager->get(\Magento\TestFramework\Response::class),
        ];
        $context = $this->objectManager->create(\Magento\Framework\App\Action\Context::class, $arguments);
        $this->_controller = $this->objectManager->create(
            \Magento\Catalog\Helper\Product\Stub\ProductControllerStub::class,
            ['context' => $context]
        );
        $resultPageFactory = $this->objectManager->get(\Magento\Framework\View\Result\PageFactory::class);
        $this->page = $resultPageFactory->create();
    }

    /**
     * Cleanup session, contaminated by product initialization methods
     */
    protected function tearDown(): void
    {
        $this->objectManager->get(\Magento\Catalog\Model\Session::class)->unsLastViewedProductId();
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
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE)
            ->setId(99)
            ->setSku('test-sku')
            ->setUrlKey($uniqid);
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = $this->objectManager;
        $objectManager->get(\Magento\Framework\Registry::class)->register('product', $product);

        $this->_helper->initProductLayout($this->page, $product);

        /** @var \Magento\Framework\View\Page\Config $pageConfig */
        $pageConfig = $this->objectManager->get(\Magento\Framework\View\Page\Config::class);
        $bodyClass = $pageConfig->getElementAttribute(
            \Magento\Framework\View\Page\Config::ELEMENT_TYPE_BODY,
            \Magento\Framework\View\Page\Config::BODY_ATTRIBUTE_CLASS
        );
        $this->assertStringContainsString("product-{$uniqid}", $bodyClass);
        $handles = $this->page->getLayout()->getUpdate()->getHandles();
        $this->assertContains('catalog_product_view_type_simple', $handles);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default_store design/head/title_prefix prefix
     * @magentoConfigFixture default_store design/head/title_suffix suffix
     * @magentoAppArea frontend
     */
    public function testPrepareAndRender()
    {
        // need for \Magento\Review\Block\Form::getProductInfo()
        $this->objectManager->get(\Magento\Framework\App\RequestInterface::class)->setParam('id', 10);

        $this->_helper->prepareAndRender($this->page, 10, $this->_controller);
        /** @var \Magento\TestFramework\Response $response */
        $response = $this->objectManager->get(\Magento\TestFramework\Response::class);
        $this->page->renderResult($response);
        $this->assertStringContainsString('prefix meta title suffix', $response->getBody());
        $this->assertNotEmpty($response->getBody());
        $this->assertEquals(
            10,
            $this->objectManager->get(
                \Magento\Catalog\Model\Session::class
            )->getLastViewedProductId()
        );
    }

    /**
     * Product meta description should be rendered on the product HTML sources as is, without changes or substitutions
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    #[
        AppIsolation(true),
        DataFixture(ProductFixture::class, ['meta_description' => 'Product Meta Description'], 'p1'),
    ]
    public function testProductMetaDescriptionShouldBeRenderedAsIs()
    {
        $product = $this->fixtures->get('p1');
        $metaDescription = '<meta name="description" content="Product Meta Description"/>';

        $this->objectManager->get(\Magento\Framework\App\RequestInterface::class)->setParam('id', $product->getId());
        $this->_helper->prepareAndRender($this->page, $product->getId(), $this->_controller);

        /** @var \Magento\TestFramework\Response $response */
        $response = $this->objectManager->get(\Magento\TestFramework\Response::class);

        $this->page->renderResult($response);

        $this->assertNotEmpty($response->getBody());
        $this->assertStringContainsString(
            $metaDescription,
            $response->getBody(),
            'Empty meta description should be rendered as is'
        );
    }

    /**
     * If the product meta description is empty, it should not be substituted with any other data and should not be
     * rendered on the product HTML sources
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    #[
        AppIsolation(true),
        DataFixture(ProductFixture::class, ['meta_description' => ''], 'p1'),
    ]
    public function testEmptyProductMetaDescriptionShouldNotBeSubstitutedAndRendered()
    {
        $product = $this->fixtures->get('p1');
        $metaDescription = '<meta name="description" content="';

        $this->objectManager->get(\Magento\Framework\App\RequestInterface::class)->setParam('id', $product->getId());
        $this->_helper->prepareAndRender($this->page, $product->getId(), $this->_controller);

        /** @var \Magento\TestFramework\Response $response */
        $response = $this->objectManager->get(\Magento\TestFramework\Response::class);

        $this->page->renderResult($response);

        $this->assertNotEmpty($response->getBody());
        $this->assertStringNotContainsStringIgnoringCase(
            $metaDescription,
            $response->getBody(),
            'Empty meta description should not be substituted or rendered'
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testPrepareAndRenderWrongController()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);

        $objectManager = $this->objectManager;
        $controller = $objectManager->create(\Magento\Catalog\Helper\Product\Stub\ProductControllerStub::class);
        $this->_helper->prepareAndRender($this->page, 10, $controller);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testPrepareAndRenderWrongProduct()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);

        $this->_helper->prepareAndRender($this->page, 999, $this->_controller);
    }
}
