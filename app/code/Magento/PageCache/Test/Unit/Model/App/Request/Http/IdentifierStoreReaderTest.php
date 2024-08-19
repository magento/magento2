<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\App\Request\Http;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\DesignExceptions;
use Magento\PageCache\Model\App\Request\Http\IdentifierStoreReader;
use Magento\PageCache\Model\Config;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IdentifierStoreReaderTest extends TestCase
{
    /**
     * @var DesignExceptions|MockObject
     */
    private $designExceptionsMock;
    /**
     * @var RequestInterface|MockObject
     */
    private MockObject|RequestInterface $requestMock;
    /**
     * @var Config|MockObject
     */
    private $configMock;
    /**
     * @var IdentifierStoreReader
     */
    private $model;

    protected function setUp(): void
    {
        $this->designExceptionsMock = $this->createPartialMock(
            DesignExceptions::class,
            ['getThemeByRequest']
        );

        $this->requestMock = $this->createMock(Http::class);

        $this->configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getType', 'isEnabled'])
            //->addMethods(['getType', 'isEnabled'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = new IdentifierStoreReader(
            $this->designExceptionsMock,
            $this->requestMock,
            $this->configMock
        );
    }

    public function testGetPageTagsWithStoreCacheTagsWhenVarnishCacheIsEnabled()
    {
        $this->configMock->expects($this->any())
            ->method('getType')
            ->willReturn(\Magento\PageCache\Model\Config::VARNISH);

        $this->requestMock->expects($this->never())->method('getServerValue');

        $data = ['anything'];
        $this->model->getPageTagsWithStoreCacheTags($data);
    }

    public function testGetPageTagsWithStoreCacheTagsWhenFPCIsDisabled()
    {
        $this->configMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn(false);

        $this->requestMock->expects($this->never())->method('getServerValue');

        $data = ['anything'];
        $this->model->getPageTagsWithStoreCacheTags($data);
    }

    public function testGetPageTagsWithStoreCacheTagsWhenStoreDataAreInContext()
    {
        $this->configMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->any())
            ->method('getType')
            ->willReturn(\Magento\PageCache\Model\Config::BUILT_IN);

        $defaultRequestMock = clone $this->requestMock;
        $defaultRequestMock->expects($this->any())
            ->method('getServerValue')
            ->willReturnCallback(
                function ($param) {
                    if ($param == StoreManager::PARAM_RUN_TYPE) {
                        return 'store';
                    }
                    if ($param == StoreManager::PARAM_RUN_CODE) {
                        return 'default';
                    }
                }
            );

        $data = ['anything'];

        $this->model = new IdentifierStoreReader(
            $this->designExceptionsMock,
            $defaultRequestMock,
            $this->configMock
        );
        $newData = $this->model->getPageTagsWithStoreCacheTags($data);

        $this->assertArrayHasKey(StoreManager::PARAM_RUN_TYPE, $newData);
        $this->assertArrayHasKey(StoreManager::PARAM_RUN_CODE, $newData);
        $this->assertEquals($newData[StoreManager::PARAM_RUN_TYPE], 'store');
        $this->assertEquals($newData[StoreManager::PARAM_RUN_CODE], 'default');
    }
}
