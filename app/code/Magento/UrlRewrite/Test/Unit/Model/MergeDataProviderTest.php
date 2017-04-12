<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Test\Unit\Model;

use Magento\UrlRewrite\Model\MergeDataProvider;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class MergeDataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MergeDataProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlRewritesSet;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->urlRewritesSet = (new ObjectManager($this))->getObject(
            MergeDataProvider::class,
            []
        );
    }

    /**
     * Run test merge method
     *
     * @param array $urlRewriteMockArray
     * @param String $expectedData
     * @param int $arrayCount
     * @dataProvider mergeDataProvider
     * @return void
     */
    public function testMerge($urlRewriteMockArray, $expectedData, $arrayCount)
    {
        $this->urlRewritesSet->merge($urlRewriteMockArray);
        $this->assertEquals($expectedData, $this->urlRewritesSet->getData());
        $this->assertCount($arrayCount, $this->urlRewritesSet->getData());
    }

    /**
     * Run test getData method when data is Empty
     *
     * @return void
     */
    public function testGetDataWhenEmpty()
    {
        $this->assertEmpty($this->urlRewritesSet->getData());
    }

    /**
     * Data provider for testMerge
     *
     * @return array
     */
    public function mergeDataProvider()
    {
        $urlRewriteMock1 = $this->getMock(UrlRewrite::class, [], [], '', false);

        $requestPathForMock2 = 'magento.tst/products/simpleproduct2';
        $storeIdForMock2 = 'testStore2';
        $urlRewriteMock2 = $this->getMock(UrlRewrite::class, [], [], '', false);

        $urlRewriteMock2->expects($this->atLeastOnce())
            ->method('getRequestPath')
            ->willReturn($requestPathForMock2);

        $urlRewriteMock2->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeIdForMock2);

        $requestPathForMock3 = 'magento.tst/products/simpleproduct3';
        $storeIdForMock3 = 'testStore3';
        $urlRewriteMock3 = $this->getMock(UrlRewrite::class, [], [], '', false);

        $urlRewriteMock3->expects($this->atLeastOnce())
            ->method('getRequestPath')
            ->willReturn($requestPathForMock3);

        $urlRewriteMock3->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn($storeIdForMock3);

        return [
            [
                [],
                [],
                0
            ],
            [
                [$urlRewriteMock1],
                [$urlRewriteMock1],
                1
            ],
            [
                [
                    $urlRewriteMock1,
                    $urlRewriteMock2,
                    $urlRewriteMock2
                ],
                [
                    $urlRewriteMock1,
                    $requestPathForMock2 . '_' . $storeIdForMock2 => $urlRewriteMock2
                ],
                2
            ],
            [
                [
                    $urlRewriteMock1,
                    $urlRewriteMock2,
                    $urlRewriteMock3
                ],
                [
                    $urlRewriteMock1,
                    $requestPathForMock2 . '_' . $storeIdForMock2 => $urlRewriteMock2,
                    $requestPathForMock3 . '_' . $storeIdForMock3 => $urlRewriteMock3
                ],
                3
            ],
        ];
    }
}
