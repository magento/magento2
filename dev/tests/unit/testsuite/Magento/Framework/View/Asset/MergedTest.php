<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Asset;

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

        $this->_logger = $this->getMock('Magento\Framework\Logger', array('logException'), array(), '', false);

        $this->_mergeStrategy = $this->getMock('Magento\Framework\View\Asset\MergeStrategyInterface');

        $this->_assetRepo = $this->getMock(
            '\Magento\Framework\View\Asset\Repository', array(), array(), '', false
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage At least one asset has to be passed for merging.
     */
    public function testConstructorNothingToMerge()
    {
        new \Magento\Framework\View\Asset\Merged($this->_logger, $this->_mergeStrategy, $this->_assetRepo, array());
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
            array($this->_assetJsOne, $assetUrl)
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
            array($this->_assetJsOne, $assetCss)
        );
    }

    public function testIteratorInterfaceMerge()
    {
        $assets = array($this->_assetJsOne, $this->_assetJsTwo);
        $this->_logger->expects($this->never())->method('logException');
        $merged = new \Magento\Framework\View\Asset\Merged(
            $this->_logger,
            $this->_mergeStrategy,
            $this->_assetRepo,
            $assets
        );
        $mergedAsset = $this->getMock('Magento\Framework\View\Asset\File', array(), array(), '', false);
        $this->_mergeStrategy
            ->expects($this->once())
            ->method('merge')
            ->with($assets, $mergedAsset)
            ->will($this->returnValue(null));
        $this->_assetRepo->expects($this->once())->method('createArbitrary')->will($this->returnValue($mergedAsset));
        $expectedResult = array($mergedAsset);

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
            array($this->_assetJsOne, $this->_assetJsTwo, $assetBroken)
        );

        $this->_logger->expects($this->once())->method('logException')->with($this->identicalTo($mergeError));

        $expectedResult = array($this->_assetJsOne, $this->_assetJsTwo, $assetBroken);
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
        $actualItems = array();
        foreach ($actual as $actualItem) {
            $actualItems[] = $actualItem;
        }
        $this->assertEquals($expectedItems, $actualItems);
    }
}
