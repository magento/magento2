<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Controller;

use Magento\Store\Model\Store;

class PathProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Store\Model\StoreManagerInterface */
    private $storeManagerMock;

    /** @var \Magento\Webapi\Controller\PathProcessor */
    private $model;

    /** @var string */
    private $arbitraryStoreCode = 'myStoreCode';

    /** @var string */
    private $endpointPath = '/V1/path/of/endpoint';

    public function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->expects($this->once())
            ->method('getStores')
            ->willReturn([$this->arbitraryStoreCode => 'store object', Store::DEFAULT_CODE => 'default store object']);
        $this->model = new \Magento\Webapi\Controller\PathProcessor($this->storeManagerMock);
    }

    /**
     * @dataProvider processPathDataProvider
     *
     * @param $storeCodeInPath
     * @param $storeCodeSet
     */
    public function testAllStoreCode($storeCodeInPath, $storeCodeSet)
    {
        $storeCodeInPath = !$storeCodeInPath ?: '/' . $storeCodeInPath; // add leading slash if store code not empty
        $inPath = 'rest' . $storeCodeInPath . $this->endpointPath;
        $this->storeManagerMock->expects($this->once())
            ->method('setCurrentStore')
            ->with($storeCodeSet);
        $result = $this->model->process($inPath);
        $this->assertSame($this->endpointPath, $result);
    }

    public function processPathDataProvider()
    {
        return [
            'All store code' => ['all', Store::ADMIN_CODE],
            'Default store code' => ['', Store::DEFAULT_CODE],
            'Arbitrary store code' => [$this->arbitraryStoreCode, $this->arbitraryStoreCode],
            'Explicit default store code' => [Store::DEFAULT_CODE, Store::DEFAULT_CODE]
        ];
    }
}
