<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer\Test\Unit;

use \Magento\Framework\Indexer\MultiDimensionProvider;
use \Magento\Framework\Indexer\DimensionProviderInterface;
use \Magento\Framework\Indexer\Dimension;

class MultiDimensionProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * tests that MultiDimensionProvider will return [[]] in case it has no dimension providers
     */
    public function testWithNoDataProviders()
    {
        // prepare expected dimensions
        $expectedDimensions = [[]];

        // collect actual dimensions
        $multiDimensionProvider = new MultiDimensionProvider([]);

        $actualDimensions = [];
        foreach ($multiDimensionProvider as $dimension) {
            $actualDimensions[] = $dimension;
        }

        $this->assertSame($expectedDimensions, $actualDimensions);
    }

    /**
     * tests multiplication of dimensions from different providers
     *
     * e.g we have three dimensions:
     *  - dimension X with values (x1, x2)
     *  - dimension Y with values (y1, y2, y3)
     *  - dimension Z with values (z1, z2)
     *
     * the multiplication result set will be:
     * x1-y1-z1
     * x1-y1-z2
     * x1-y2-z1
     * x1-y2-z2
     * x1-y3-z1
     * x1-y3-z2
     * x2-y1-z1
     * x2-y1-z2
     * x2-y2-z1
     * x2-y2-z2
     * x2-y3-z1
     * x2-y3-z2
     */
    public function testWithMultipleDataProviders()
    {
        // prepare expected dimensions
        $dimensionXData = [
            $this->getDimensionMock('x', 1),
            $this->getDimensionMock('x', 2),
            $this->getDimensionMock('x', 3),
        ];

        $dimensionYData = [
            $this->getDimensionMock('y', 1),
            $this->getDimensionMock('y', 2),
            $this->getDimensionMock('y', 3),
            $this->getDimensionMock('y', 4),
            $this->getDimensionMock('y', 5),
        ];

        $dimensionZData = [
            $this->getDimensionMock('z', 1),
            $this->getDimensionMock('z', 2),
        ];

        $expectedDimensions = [];

        foreach ($dimensionXData as $dimensionX) {
            foreach ($dimensionYData as $dimensionY) {
                foreach ($dimensionZData as $dimensionZ) {
                    $expectedDimensions[] = [
                        $dimensionX->getName() => $dimensionX,
                        $dimensionY->getName() => $dimensionY,
                        $dimensionZ->getName() => $dimensionZ,
                    ];
                }
            }
        }

        // collect actual dimensions
        $multiDimensionProvider = new MultiDimensionProvider(
            [
                $this->getDimensionProviderMock($dimensionXData),
                $this->getDimensionProviderMock($dimensionYData),
                $this->getDimensionProviderMock($dimensionZData),
            ]
        );

        $actualDimensions = [];
        foreach ($multiDimensionProvider as $dimension) {
            $actualDimensions[] = $dimension;
        }

        $this->assertSame($expectedDimensions, $actualDimensions);
    }

    /**
     * tests that the same MultiDimensionProvider can be used in foreach multiple times without creating again
     */
    public function testMultiDimensionProviderIsReIterable()
    {
        // prepare expected dimensions
        $dimensionXData = [
            $this->getDimensionMock('x', 1),
            $this->getDimensionMock('x', 2),
            $this->getDimensionMock('x', 3),
        ];

        $dimensionZData = [
            $this->getDimensionMock('z', 1),
            $this->getDimensionMock('z', 2),
        ];

        // collect actual dimensions
        $multiDimensionProvider = new MultiDimensionProvider(
            [
                $this->getDimensionProviderMock($dimensionXData),
                $this->getDimensionProviderMock($dimensionZData),
            ]
        );

        // first iteration
        $actualDimensions1st = [];
        foreach ($multiDimensionProvider as $dimension) {
            $actualDimensions1st[] = $dimension;
        }

        // second iteration
        $actualDimensions2nd = [];
        foreach ($multiDimensionProvider as $dimension) {
            $actualDimensions2nd[] = $dimension;
        }

        $this->assertSame($actualDimensions1st, $actualDimensions2nd);
    }

    /**
     * tests that MultiDimensionProvider will throw exception when all dimension providers has nothing to return
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Can`t multiple dimensions because some of them are empty.
     */
    public function testMultiDimensionProviderWithEmptyDataProvider()
    {
        // collect actual dimensions
        $multiDimensionProvider = new MultiDimensionProvider(
            [
                $this->getDimensionProviderMock([]),
                $this->getDimensionProviderMock([]),
            ]
        );

        $actualDimensions = [];
        foreach ($multiDimensionProvider as $dimension) {
            $actualDimensions[] = $dimension;
        }
    }

    /**
     * tests that MultiDimensionProvider will throw exception when one dimension providers has nothing to return
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Can`t multiple dimensions because some of them are empty.
     */
    public function testMultiDimensionProviderWithMixedDataProvider()
    {

        // prepare expected dimensions
        $dimensionXData = [
            $this->getDimensionMock('x', 1),
            $this->getDimensionMock('x', 2),
            $this->getDimensionMock('x', 3),
        ];

        $dimensionYData = [
            $this->getDimensionMock('y', 1),
            $this->getDimensionMock('y', 2),
            $this->getDimensionMock('y', 3),
            $this->getDimensionMock('y', 4),
            $this->getDimensionMock('y', 5),
        ];

        $dimensionZData = [];

        // collect actual dimensions
        $multiDimensionProvider = new MultiDimensionProvider(
            [
                $this->getDimensionProviderMock($dimensionXData),
                $this->getDimensionProviderMock($dimensionYData),
                $this->getDimensionProviderMock($dimensionZData),
            ]
        );

        $actualDimensions = [];
        foreach ($multiDimensionProvider as $dimension) {
            $actualDimensions[] = $dimension;
        }
    }

    private function getDimensionProviderMock($dimensions)
    {
        $dimensionProviderMock = $this->getMockBuilder(DimensionProviderInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMethods(['getIterator'])
            ->getMockForAbstractClass();

        $dimensionProviderMock->expects($this->any())
            ->method('getIterator')
            ->will(
                $this->returnCallback(
                    function () use ($dimensions) {
                        return \SplFixedArray::fromArray($dimensions);
                    }
                )
            );

        return $dimensionProviderMock;
    }

    private function getDimensionMock(string $name, string $value)
    {
        $dimensionMock = $this->getMockBuilder(Dimension::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMethods(['getName', 'getValue'])
            ->getMock();

        $dimensionMock->expects($this->any())
            ->method('getName')
            ->willReturn($name);

        $dimensionMock->expects($this->any())
            ->method('getValue')
            ->willReturn($value);

        return $dimensionMock;
    }
}
