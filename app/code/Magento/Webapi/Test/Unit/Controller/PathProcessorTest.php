<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
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

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->expects($this->once())
            ->method('getStores')
            ->willReturn([$this->arbitraryStoreCode => 'store object', 'default' => 'default store object']);
        $this->model = new \Magento\Webapi\Controller\PathProcessor($this->storeManagerMock);
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
            'Explicit default store code' => ['default', 'default']
        ];
    }
}
