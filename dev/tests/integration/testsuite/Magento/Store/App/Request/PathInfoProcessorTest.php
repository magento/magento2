<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Request;

use \Magento\TestFramework\Helper\Bootstrap;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Store\Model\Store;

class PathInfoProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\App\Request\PathInfoProcessor
     */
    protected $pathProcessor;

    protected function setUp()
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
        $this->assertEquals($pathInfo, $this->pathProcessor->process($request, $pathInfo));
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
    public function testProcessValidStoreCodeCase1()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = Bootstrap::getObjectManager()->get(\Magento\Store\Model\Store::class);
        $store->load('fixturestore', 'code');

        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = Bootstrap::getObjectManager()->create(\Magento\Framework\App\RequestInterface::class);

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $config->setValue(Store::XML_PATH_STORE_IN_URL, false, ScopeInterface::SCOPE_STORE, $store->getCode());
        $pathInfo = sprintf('/%s/m/c/a', $store->getCode());
        $this->assertEquals($pathInfo, $this->pathProcessor->process($request, $pathInfo));
    }

    /**
     * @covers \Magento\Store\App\Request\PathInfoProcessor::process
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testProcessValidStoreCodeCase2()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = Bootstrap::getObjectManager()->get(\Magento\Store\Model\Store::class);
        $store->load('fixturestore', 'code');

        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = Bootstrap::getObjectManager()->create(\Magento\Framework\App\RequestInterface::class);

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $config->setValue(Store::XML_PATH_STORE_IN_URL, true, ScopeInterface::SCOPE_STORE, $store->getCode());
        $pathInfo = sprintf('/%s/m/c/a', $store->getCode());
        $this->assertEquals('/m/c/a', $this->pathProcessor->process($request, $pathInfo));
    }

    /**
     * @covers \Magento\Store\App\Request\PathInfoProcessor::process
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testProcessValidStoreCodeCase3()
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
        $config->setValue(Store::XML_PATH_STORE_IN_URL, true, ScopeInterface::SCOPE_STORE, $store->getCode());
        $pathInfo = sprintf('/%s/m/c/a', $store->getCode());
        $this->assertEquals($pathInfo, $this->pathProcessor->process($request, $pathInfo));
        $this->assertEquals('noroute', $request->getActionName());
    }
}
