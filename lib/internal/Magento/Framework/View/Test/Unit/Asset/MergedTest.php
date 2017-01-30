<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\View\Test\Unit\Asset;

class MergedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mergeStrategy;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_assetJsOne;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_assetJsTwo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_assetRepo;

    protected function setUp()
    {
        $this->_assetJsOne = $this->getMockForAbstractClass('Magento\Framework\View\Asset\MergeableInterface');
        $this->_assetJsOne->expects($this->any())->method('getContentType')->will($this->returnValue('js'));
        $this->_assetJsOne->expects($this->any())->method('getPath')
            ->will($this->returnValue('script_one.js'));

        $this->_assetJsTwo = $this->getMockForAbstractClass('Magento\Framework\View\Asset\MergeableInterface');
        $this->_assetJsTwo->expects($this->any())->method('getContentType')->will($this->returnValue('js'));
        $this->_assetJsTwo->expects($this->any())->method('getPath')
            ->will($this->returnValue('script_two.js'));

        $this->_logger = $this->getMock('Psr\Log\LoggerInterface');

        $this->_mergeStrategy = $this->getMock('Magento\Framework\View\Asset\MergeStrategyInterface');

        $this->_assetRepo = $this->getMock(
            '\Magento\Framework\View\Asset\Repository', [], [], '', false
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage At least one asset has to be passed for merging.
     */
    public function testConstructorNothingToMerge()
    {
        new \Magento\Framework\View\Asset\Merged($this->_logger, $this->_mergeStrategy, $this->_assetRepo, []);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Asset has to implement \Magento\Framework\View\Asset\MergeableInterface.
     */
    public function testConstructorRequireMergeInterface()
    {
        $assetUrl = new \Magento\Framework\View\Asset\Remote('http://example.com/style.css', 'css');
        new \Magento\Framework\View\Asset\Merged(
            $this->_logger,
            $this->_mergeStrategy,
            $this->_assetRepo,
            [$this->_assetJsOne, $assetUrl]
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Content type 'css' cannot be merged with 'js'.
     */
    public function testConstructorIncompatibleContentTypes()
    {
        $assetCss = $this->getMockForAbstractClass('Magento\Framework\View\Asset\MergeableInterface');
        $assetCss->expects($this->any())->method('getContentType')->will($this->returnValue('css'));
        new \Magento\Framework\View\Asset\Merged(
            $this->_logger,
            $this->_mergeStrategy,
            $this->_assetRepo,
            [$this->_assetJsOne, $assetCss]
        );
    }

    public function testIteratorInterfaceMerge()
    {
        $assets = [$this->_assetJsOne, $this->_assetJsTwo];
        $this->_logger->expects($this->never())->method('critical');
        $merged = new \Magento\Framework\View\Asset\Merged(
            $this->_logger,
            $this->_mergeStrategy,
            $this->_assetRepo,
            $assets
        );
        $mergedAsset = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $this->_mergeStrategy
            ->expects($this->once())
            ->method('merge')
            ->with($assets, $mergedAsset)
            ->will($this->returnValue(null));
        $this->_assetRepo->expects($this->once())->method('createArbitrary')->will($this->returnValue($mergedAsset));
        $expectedResult = [$mergedAsset];

        $this->_assertIteratorEquals($expectedResult, $merged);
        $this->_assertIteratorEquals($expectedResult, $merged); // ensure merging happens only once
    }

    public function testIteratorInterfaceMergeFailure()
    {
        $mergeError = new \Exception('File not found');
        $assetBroken = $this->getMockForAbstractClass('Magento\Framework\View\Asset\MergeableInterface');
        $assetBroken->expects($this->any())->method('getContentType')->will($this->returnValue('js'));
        $assetBroken->expects($this->any())->method('getPath')
            ->will($this->throwException($mergeError));

        $merged = new \Magento\Framework\View\Asset\Merged(
            $this->_logger,
            $this->_mergeStrategy,
            $this->_assetRepo,
            [$this->_assetJsOne, $this->_assetJsTwo, $assetBroken]
        );

        $this->_logger->expects($this->once())->method('critical')->with($this->identicalTo($mergeError));

        $expectedResult = [$this->_assetJsOne, $this->_assetJsTwo, $assetBroken];
        $this->_assertIteratorEquals($expectedResult, $merged);
        $this->_assertIteratorEquals($expectedResult, $merged); // ensure merging attempt happens only once
    }

    /**
     * Assert that iterator items equal to expected ones
     *
     * @param array $expectedItems
     * @param \Iterator $actual
     */
    protected function _assertIteratorEquals(array $expectedItems, \Iterator $actual)
    {
        $actualItems = [];
        foreach ($actual as $actualItem) {
            $actualItems[] = $actualItem;
        }
        $this->assertEquals($expectedItems, $actualItems);
    }
}
