<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Controller;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Webapi\Controller\PathProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Webapi\Controller\PathProcessor class.
 */
class PathProcessorTest extends TestCase
{
    /** @var MockObject|StoreManagerInterface */
    private $storeManagerMock;

    /** @var MockObject|ResolverInterface */
    private $localeResolverMock;

    /** @var PathProcessor */
    private $model;

    /** @var string */
    private static $arbitraryStoreCode = 'myStoreCode';

    /** @var string */
    private $endpointPath = '/V1/path/of/endpoint';

    protected function setUp(): void
    {
        $store = $this->getMockForAbstractClass(StoreInterface::class);
        $store->method('getId')->willReturn(2);

        $this->storeManagerMock = $this->createConfiguredMock(
            StoreManagerInterface::class,
            [
                'getStores' => [self::$arbitraryStoreCode => 'store object', 'default' => 'default store object'],
                'getStore'  => $store,
            ]
        );
        $this->storeManagerMock->expects($this->once())->method('getStores');

        $this->localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->localeResolverMock->method('emulate')->with(2);

        $this->model = new PathProcessor($this->storeManagerMock, $this->localeResolverMock);
    }

    /**
     * @dataProvider processPathDataProvider
     *
     * @param string $storeCodeInPath
     * @param string $storeCodeSet
     * @param int $setCurrentStoreCallCtr
     */
    public function testAllStoreCode($storeCodeInPath, $storeCodeSet, $setCurrentStoreCallCtr = 1)
    {
        $storeCodeInPath = !$storeCodeInPath ?: '/' . $storeCodeInPath; // add leading slash if store code not empty
        $inPath = 'rest' . $storeCodeInPath . $this->endpointPath;
        $this->storeManagerMock->expects($this->exactly($setCurrentStoreCallCtr))
            ->method('setCurrentStore')
            ->with($storeCodeSet);
        if ($setCurrentStoreCallCtr > 0) {
            $this->localeResolverMock->expects($this->once())
                ->method('emulate');
        }
        $result = $this->model->process($inPath);
        $this->assertSame($this->endpointPath, $result);
    }

    /**
     * @return array
     */
    public static function processPathDataProvider()
    {
        return [
            'All store code' => ['all', Store::ADMIN_CODE],
            'Default store code' => ['', 'default', 0],
            'Arbitrary store code' => [self::$arbitraryStoreCode, self::$arbitraryStoreCode],
            'Explicit default store code' => ['default', 'default'],
        ];
    }
}
