<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Request;

use \Magento\TestFramework\Helper\Bootstrap;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Store\Model\Store;

class PathInfoProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\App\Request\PathInfoProcessor
     */
    private $pathProcessor;

    protected function setUp(): void
    {
        $this->pathProcessor = Bootstrap::getObjectManager()->create(
            \Magento\Store\App\Request\PathInfoProcessor::class
        );
    }

    /**
     * @covers \Magento\Store\App\Request\PathInfoProcessor::process
     * @dataProvider notValidStoreCodeDataProvider
     */
    public function testProcessNotValidStoreCode($pathInfo)
    {
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = Bootstrap::getObjectManager()->create(\Magento\Framework\App\RequestInterface::class);
        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $config->setValue(Store::XML_PATH_STORE_IN_URL, true);
        $info = $this->pathProcessor->process($request, $pathInfo);
        $this->assertEquals($pathInfo, $info);
    }

    public function notValidStoreCodeDataProvider()
    {
        return [
            ['not_valid_store_code_int' => '/100500/m/c/a'],
            ['not_valid_store_code_str' => '/test_string/m/c/a'],
        ];
    }

    /**
     * @covers \Magento\Store\App\Request\PathInfoProcessor::process
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testProcessValidStoreDisabledStoreUrl()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = Bootstrap::getObjectManager()->get(\Magento\Store\Model\Store::class);
        $store->load('fixturestore', 'code');

        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = Bootstrap::getObjectManager()->create(\Magento\Framework\App\RequestInterface::class);

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $config->setValue(Store::XML_PATH_STORE_IN_URL, true);
        $config->setValue(Store::XML_PATH_STORE_IN_URL, false, ScopeInterface::SCOPE_STORE, $store->getCode());
        $pathInfo = sprintf('/%s/m/c/a', $store->getCode());
        $this->assertEquals($pathInfo, $this->pathProcessor->process($request, $pathInfo));
    }

    /**
     * @covers \Magento\Store\App\Request\PathInfoProcessor::process
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testProcessValidStoreCodeCaseProcessStoreName()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = Bootstrap::getObjectManager()->get(\Magento\Store\Model\Store::class);
        $store->load('fixturestore', 'code');

        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = Bootstrap::getObjectManager()->create(\Magento\Framework\App\RequestInterface::class);

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $config->setValue(Store::XML_PATH_STORE_IN_URL, true);
        $config->setValue(Store::XML_PATH_STORE_IN_URL, true, ScopeInterface::SCOPE_STORE, $store->getCode());
        $pathInfo = sprintf('/%s/m/c/a', $store->getCode());
        $this->assertEquals('/m/c/a', $this->pathProcessor->process($request, $pathInfo));
    }

    /**
     * @covers \Magento\Store\App\Request\PathInfoProcessor::process
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testProcessValidStoreCodeWhenStoreIsDirectFrontNameWithFrontName()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = Bootstrap::getObjectManager()->get(\Magento\Store\Model\Store::class);
        $store->load('fixturestore', 'code');

        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\RequestInterface::class,
            ['directFrontNames' => [$store->getCode() => true]]
        );

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $config->setValue(Store::XML_PATH_STORE_IN_URL, true);
        $config->setValue(Store::XML_PATH_STORE_IN_URL, true, ScopeInterface::SCOPE_STORE, $store->getCode());
        $pathInfo = sprintf('/%s/m/c/a', $store->getCode());
        $this->assertEquals($pathInfo, $this->pathProcessor->process($request, $pathInfo));
        $this->assertEquals(\Magento\Framework\App\Router\Base::NO_ROUTE, $request->getActionName());
    }

    /**
     * @covers \Magento\Store\App\Request\PathInfoProcessor::process
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testProcessValidStoreCodeWhenStoreCodeInUrlIsDisabledWithFrontName()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = Bootstrap::getObjectManager()->get(\Magento\Store\Model\Store::class);
        $store->load('fixturestore', 'code');

        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\RequestInterface::class,
            ['directFrontNames' => ['someFrontName' => true]]
        );

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $config->setValue(Store::XML_PATH_STORE_IN_URL, true);
        $config->setValue(Store::XML_PATH_STORE_IN_URL, false, ScopeInterface::SCOPE_STORE, $store->getCode());
        $pathInfo = sprintf('/%s/m/c/a', $store->getCode());
        $this->assertEquals($pathInfo, $this->pathProcessor->process($request, $pathInfo));
    }

    /**
     * @covers \Magento\Store\App\Request\PathInfoProcessor::process
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testProcessValidStoreCodeWhenStoreCodeisAdmin()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = Bootstrap::getObjectManager()->get(\Magento\Store\Model\Store::class);
        $store->load('fixturestore', 'code');

        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\RequestInterface::class,
            ['directFrontNames' => ['someFrontName' => true]]
        );

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $config->setValue(Store::XML_PATH_STORE_IN_URL, true);
        $config->setValue(Store::XML_PATH_STORE_IN_URL, false, ScopeInterface::SCOPE_STORE, $store->getCode());
        $pathInfo = sprintf('/%s/m/c/a', 'admin');
        $this->assertEquals($pathInfo, $this->pathProcessor->process($request, $pathInfo));
    }

    /**
     * @covers \Magento\Store\App\Request\PathInfoProcessor::process
     */
    public function testProcessValidStoreCodeWhenUrlConfigIsDisabled()
    {
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\RequestInterface::class,
            ['directFrontNames' => ['someFrontName' => true]]
        );

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $config->setValue(Store::XML_PATH_STORE_IN_URL, false);
        $pathInfo = sprintf('/%s/m/c/a', 'whatever');
        $this->assertEquals($pathInfo, $this->pathProcessor->process($request, $pathInfo));
        $this->assertNull($request->getActionName());
    }
}
