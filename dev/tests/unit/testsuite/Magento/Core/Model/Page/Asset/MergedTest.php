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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\Page\Asset;

class MergedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Page\Asset\Merged
     */
    protected $_object;

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
    protected $_dirs;

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

    protected function setUp()
    {
        $this->_assetJsOne = $this->getMockForAbstractClass('Magento\Core\Model\Page\Asset\MergeableInterface');
        $this->_assetJsOne->expects($this->any())->method('getContentType')->will($this->returnValue('js'));
        $this->_assetJsOne->expects($this->any())->method('getSourceFile')
            ->will($this->returnValue('/pub/script_one.js'));

        $this->_assetJsTwo = $this->getMockForAbstractClass('Magento\Core\Model\Page\Asset\MergeableInterface');
        $this->_assetJsTwo->expects($this->any())->method('getContentType')->will($this->returnValue('js'));
        $this->_assetJsTwo->expects($this->any())->method('getSourceFile')
            ->will($this->returnValue('/pub/script_two.js'));

        $this->_logger = $this->getMock('Magento\Core\Model\Logger', array('logException'), array(), '', false);

        $this->_dirs = $this->getMock('Magento\App\Dir', array(), array(), '', false);

        $this->_mergeStrategy = $this->getMock('Magento\Core\Model\Page\Asset\MergeStrategyInterface');

        $this->_objectManager = $this->getMockForAbstractClass(
            'Magento\ObjectManager', array(), '', true, true, true, array('create')
        );

        $this->_object = new \Magento\Core\Model\Page\Asset\Merged(
            $this->_objectManager, $this->_logger, $this->_dirs, $this->_mergeStrategy,
            array($this->_assetJsOne, $this->_assetJsTwo)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage At least one asset has to be passed for merging.
     */
    public function testConstructorNothingToMerge()
    {
        $this->_object = new \Magento\Core\Model\Page\Asset\Merged(
            $this->_objectManager, $this->_logger, $this->_dirs, $this->_mergeStrategy, array()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Asset has to implement \Magento\Core\Model\Page\Asset\MergeableInterface.
     */
    public function testConstructorRequireMergeInterface()
    {
        $assetUrl = new \Magento\Core\Model\Page\Asset\Remote('http://example.com/style.css', 'css');
        $this->_object = new \Magento\Core\Model\Page\Asset\Merged(
            $this->_objectManager, $this->_logger, $this->_dirs, $this->_mergeStrategy,
            array($this->_assetJsOne, $assetUrl)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Content type 'css' cannot be merged with 'js'.
     */
    public function testConstructorIncompatibleContentTypes()
    {
        $assetCss = $this->getMockForAbstractClass('Magento\Core\Model\Page\Asset\MergeableInterface');
        $assetCss->expects($this->any())->method('getContentType')->will($this->returnValue('css'));
        $assetCss->expects($this->any())->method('getSourceFile')->will($this->returnValue('style.css'));

        $this->_object = new \Magento\Core\Model\Page\Asset\Merged(
            $this->_objectManager, $this->_logger, $this->_dirs, $this->_mergeStrategy,
            array($this->_assetJsOne, $assetCss)
        );
    }

    public function testIteratorInterfaceMerge()
    {
        $mergedFile = '/_merged/19b2d7c942efeb2327eadbcf04635b02.js';

        $this->_logger->expects($this->never())->method('logException');

        $publicFiles = array(
            '/pub/script_one.js' => '/pub/script_one.js',
            '/pub/script_two.js' => '/pub/script_two.js'
        );

        $this->_mergeStrategy
            ->expects($this->once())
            ->method('mergeFiles')
            ->with($publicFiles, $mergedFile, 'js')
            ->will($this->returnValue(null));

        $mergedAsset = $this->getMockForAbstractClass('Magento\Core\Model\Page\Asset\MergeableInterface');
        $this->_objectManager
            ->expects($this->once())
            ->method('create')
            ->with('Magento\Core\Model\Page\Asset\PublicFile', array('file' => $mergedFile, 'contentType' => 'js'))
            ->will($this->returnValue($mergedAsset))
        ;

        $expectedResult = array($mergedAsset);
        $this->_assertIteratorEquals($expectedResult, $this->_object);
        $this->_assertIteratorEquals($expectedResult, $this->_object); // ensure merging happens only once
    }

    public function testIteratorInterfaceMergeFailure()
    {
        $mergeError = new \Exception('File not found');
        $assetBroken = $this->getMockForAbstractClass('Magento\Core\Model\Page\Asset\MergeableInterface');
        $assetBroken->expects($this->any())->method('getContentType')->will($this->returnValue('js'));
        $assetBroken->expects($this->any())->method('getSourceFile')
            ->will($this->throwException($mergeError));

        $this->_object = new \Magento\Core\Model\Page\Asset\Merged(
            $this->_objectManager, $this->_logger, $this->_dirs, $this->_mergeStrategy,
            array($this->_assetJsOne, $this->_assetJsTwo, $assetBroken)
        );


        $this->_objectManager->expects($this->never())->method('create');
        $this->_logger->expects($this->once())->method('logException')->with($this->identicalTo($mergeError));

        $expectedResult = array($this->_assetJsOne, $this->_assetJsTwo, $assetBroken);
        $this->_assertIteratorEquals($expectedResult, $this->_object);
        $this->_assertIteratorEquals($expectedResult, $this->_object); // ensure merging attempt happens only once
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
