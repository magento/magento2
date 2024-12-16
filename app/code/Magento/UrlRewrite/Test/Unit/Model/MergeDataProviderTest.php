<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\MergeDataProvider;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MergeDataProviderTest extends TestCase
{
    /**
     * @var MergeDataProvider|MockObject
     */
    private $urlRewritesSet;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
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
     * @param array $expectedData
     * @param int $arrayCount
     * @dataProvider mergeDataProvider
     * @return void
     */
    public function testMerge($urlRewriteMockArray, $expectedData, $arrayCount)
    {
        $urlRewriteMockArrayFinal = [];
        if (!empty($urlRewriteMockArray)) {
            foreach ($urlRewriteMockArray as $key => $value) {
                $urlRewriteMockArrayFinal[$key] = $value($this);
            }
        }
        else {
            $urlRewriteMockArrayFinal = $urlRewriteMockArray;
        }

        $expectedDataFinal = [];

        if (!empty($expectedData)) {
            foreach ($expectedData as $key => $value) {
                $expectedDataFinal[$key] = $value($this);
            }
        }
        else {
            $expectedDataFinal = $expectedData;
        }

        $this->urlRewritesSet->merge($urlRewriteMockArrayFinal);
        $this->assertEquals($expectedDataFinal, $this->urlRewritesSet->getData());
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

    protected function getMockForUrlRewrite($requestPathForMock, $storeIdForMock) {
        $urlRewriteMock = $this->createMock(UrlRewrite::class);
        if ($requestPathForMock!=null && $storeIdForMock!=null) {
            $urlRewriteMock->expects($this->any())
                ->method('getRequestPath')
                ->willReturn($requestPathForMock);

            $urlRewriteMock->expects($this->any())
                ->method('getStoreId')
                ->willReturn($storeIdForMock);
        }
        return $urlRewriteMock;
    }

    /**
     * Data provider for testMerge
     *
     * @return array
     */
    public static function mergeDataProvider()
    {
        $urlRewriteMock1 = static fn (self $testCase) =>
            $testCase->getMockForUrlRewrite(null, null);

        $requestPathForMock2 = 'magento.tst/products/simpleproduct2';
        $storeIdForMock2 = 'testStore2';
        $urlRewriteMock2 = static fn (self $testCase) =>
            $testCase->getMockForUrlRewrite($requestPathForMock2, $storeIdForMock2);

        $requestPathForMock3 = 'magento.tst/products/simpleproduct3';
        $storeIdForMock3 = 'testStore3';
        $urlRewriteMock3 = static fn (self $testCase) =>
            $testCase->getMockForUrlRewrite($requestPathForMock3, $storeIdForMock3);

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
