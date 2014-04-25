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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\File\Storage;

/**
 * Class MediaTest
 */
class MediaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\File\Storage\File
     */
    protected $_model;

    /**
     * @var \Magento\Core\Helper\File\Media
     */
    protected $_loggerMock;

    /**
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $_storageHelperMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_mediaHelperMock;

    /**
     * @var \Magento\Core\Model\Resource\File\Storage\File
     */
    protected $_fileUtilityMock;

    protected function setUp()
    {
        $this->_loggerMock = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
        $this->_storageHelperMock = $this->getMock(
            'Magento\Core\Helper\File\Storage\Database',
            array(),
            array(),
            '',
            false
        );
        $this->_mediaHelperMock = $this->getMock('Magento\Core\Helper\File\Media', array(), array(), '', false);
        $this->_fileUtilityMock = $this->getMock(
            'Magento\Core\Model\Resource\File\Storage\File',
            array(),
            array(),
            '',
            false
        );

        $this->_model = new \Magento\Core\Model\File\Storage\File(
            $this->_loggerMock,
            $this->_storageHelperMock,
            $this->_mediaHelperMock,
            $this->_fileUtilityMock
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testCollectDataSuccess()
    {
        $this->_fileUtilityMock->expects(
            $this->any()
        )->method(
            'getStorageData'
        )->will(
            $this->returnValue(array('files' => array('value1', 'value2')))
        );
        $this->assertEmpty(array_diff($this->_model->collectData(0, 1), array('value1')));
    }

    public function testCollectDataFailureWrongType()
    {
        $this->_fileUtilityMock->expects(
            $this->any()
        )->method(
            'getStorageData'
        )->will(
            $this->returnValue(array('files' => array('value1', 'value2')))
        );
        $this->assertFalse($this->_model->collectData(0, 1, 'some-wrong-key'));
    }

    public function testCollectDataFailureEmptyDataWasGiven()
    {
        $this->_fileUtilityMock->expects(
            $this->any()
        )->method(
            'getStorageData'
        )->will(
            $this->returnValue(array('files' => array()))
        );
        $this->assertFalse($this->_model->collectData(0, 1));
    }
}
