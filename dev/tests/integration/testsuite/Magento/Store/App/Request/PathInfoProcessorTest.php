<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Request;

use Magento\Framework\App\RequestInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class PathInfoProcessorTest extends TestCase
{
    /**
     * @var PathInfoProcessor
     */
    private $pathProcessor;

    protected function setUp(): void
    {
        $this->pathProcessor = Bootstrap::getObjectManager()->create(PathInfoProcessor::class);
    }

    /**
     * @covers \Magento\Store\App\Request\PathInfoProcessor::process
     * @magentoConfigFixture web/url/use_store 1
     * @dataProvider notValidStoreCodeDataProvider
     * @param string $pathInfo
     */
    public function testProcessNotValidStoreCode(string $pathInfo)
    {
        $request = Bootstrap::getObjectManager()->create(RequestInterface::class);
        $info = $this->pathProcessor->process($request, $pathInfo);
        $this->assertEquals($pathInfo, $info);
    }

    public function notValidStoreCodeDataProvider(): array
    {
        return [
            ['default store id' => '/0/m/c/a'],
            ['main store id' => '/1/m/c/a'],
            ['nonexistent store code' => '/test_string/m/c/a'],
            ['admin store code' => '/admin/m/c/a'],
            ['empty path' => '/'],
        ];
    }

    /**
     * @covers \Magento\Store\App\Request\PathInfoProcessor::process
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoConfigFixture web/url/use_store 1
     */
    public function testProcessValidStoreCodeCaseProcessStoreName()
    {
        $storeCode = 'fixturestore';
        $request = Bootstrap::getObjectManager()->create(RequestInterface::class);
        $pathInfo = sprintf('/%s/m/c/a', $storeCode);
        $this->assertEquals('/m/c/a', $this->pathProcessor->process($request, $pathInfo));
    }

    /**
     * @covers \Magento\Store\App\Request\PathInfoProcessor::process
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoConfigFixture web/url/use_store 1
     */
    public function testProcessValidStoreCodeWhenStoreIsDirectFrontNameWithFrontName()
    {
        $storeCode = 'fixturestore';
        $request = Bootstrap::getObjectManager()->create(
            RequestInterface::class,
            ['directFrontNames' => [$storeCode => true]]
        );
        $pathInfo = sprintf('/%s/m/c/a', $storeCode);
        $this->assertEquals($pathInfo, $this->pathProcessor->process($request, $pathInfo));
        $this->assertEquals(\Magento\Framework\App\Router\Base::NO_ROUTE, $request->getActionName());
    }

    /**
     * @covers \Magento\Store\App\Request\PathInfoProcessor::process
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoConfigFixture web/url/use_store 0
     */
    public function testProcessValidStoreCodeWhenUrlConfigIsDisabled()
    {
        $storeCode = 'fixturestore';
        $request = Bootstrap::getObjectManager()->create(RequestInterface::class);
        $pathInfo = sprintf('/%s/m/c/a', $storeCode);
        $this->assertEquals($pathInfo, $this->pathProcessor->process($request, $pathInfo));
        $this->assertNull($request->getActionName());
    }
}
