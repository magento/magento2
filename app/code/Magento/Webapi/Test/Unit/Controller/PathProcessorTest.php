<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Controller;

use Magento\Store\Model\Store;

/**
 * Test for Magento\Webapi\Controller\PathProcessor class.
 */
class PathProcessorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Store\Model\StoreManagerInterface */
    private $storeManagerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Locale\ResolverInterface */
    private $localeResolverMock;

    /** @var \Magento\Webapi\Controller\PathProcessor */
    private $model;

    /** @var string */
    private $arbitraryStoreCode = 'myStoreCode';

    /** @var string */
    private $endpointPath = '/V1/path/of/endpoint';

    protected function setUp()
    {
        $store = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $store->method('getId')->willReturn(2);

        $this->storeManagerMock = $this->createConfiguredMock(
            \Magento\Store\Model\StoreManagerInterface::class,
            [
                'getStores' => [$this->arbitraryStoreCode => 'store object', 'default' => 'default store object'],
                'getStore'  => $store,
            ]
        );
        $this->storeManagerMock->expects($this->once())->method('getStores');

        $this->localeResolverMock = $this->createMock(\Magento\Framework\Locale\ResolverInterface::class);
        $this->localeResolverMock->method('emulate')->with(2);

        $this->model = new \Magento\Webapi\Controller\PathProcessor($this->storeManagerMock, $this->localeResolverMock);
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
    public function processPathDataProvider()
    {
        return [
            'All store code' => ['all', Store::ADMIN_CODE],
            'Default store code' => ['', 'default', 0],
            'Arbitrary store code' => [$this->arbitraryStoreCode, $this->arbitraryStoreCode],
            'Explicit default store code' => ['default', 'default'],
        ];
    }
}
