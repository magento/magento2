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
 * @category    Mage
 * @package     Mage_Theme
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Theme_Block_Adminhtml_Wysiwyg_Files_ContentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Url|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilder;

    /**
     * @var Mage_Theme_Helper_Storage|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperStorage;

    /**
     * @var Mage_Theme_Block_Adminhtml_Wysiwyg_Files_Content|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesContent;

    /**
     * @var Mage_Core_Controller_Request_Http|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    public function setUp()
    {
        $this->_helperStorage = $this->getMock('Mage_Theme_Helper_Storage', array(), array(), '', false);
        $this->_urlBuilder = $this->getMock('Mage_Backend_Model_Url', array(), array(), '', false);
        $this->_request = $this->getMock('Mage_Core_Controller_Request_Http', array(), array(), '', false);

        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $constructArguments =  $objectManagerHelper->getConstructArguments(
            'Mage_Theme_Block_Adminhtml_Wysiwyg_Files_Content',
            array(
                'urlBuilder'    => $this->_urlBuilder,
                'request'       => $this->_request
            )
        );
        $this->_filesContent = $this->getMock(
            'Mage_Theme_Block_Adminhtml_Wysiwyg_Files_Content', array('helper'), $constructArguments
        );

        $this->_filesContent->expects($this->any())
            ->method('helper')
            ->with('Mage_Theme_Helper_Storage')
            ->will($this->returnValue($this->_helperStorage));
    }

    /**
     * @dataProvider requestParamsProvider
     * @param array $requestParams
     */
    public function testGetNewFolderUrl($requestParams)
    {
        $expectedUrl = 'some_url';

        $this->_helperStorage->expects($this->once())
            ->method('getRequestParams')
            ->will($this->returnValue($requestParams));

        $this->_urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/newFolder', $requestParams)
            ->will($this->returnValue($expectedUrl));

        $this->assertEquals($expectedUrl, $this->_filesContent->getNewfolderUrl());
    }

    /**
     * @dataProvider requestParamsProvider
     * @param array $requestParams
     */
    public function testGetDeleteFilesUrl($requestParams)
    {
        $expectedUrl = 'some_url';

        $this->_helperStorage->expects($this->once())
            ->method('getRequestParams')
            ->will($this->returnValue($requestParams));

        $this->_urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/deleteFiles', $requestParams)
            ->will($this->returnValue($expectedUrl));

        $this->assertEquals($expectedUrl, $this->_filesContent->getDeleteFilesUrl());
    }

    /**
     * @dataProvider requestParamsProvider
     * @param array $requestParams
     */
    public function testGetOnInsertUrl($requestParams)
    {
        $expectedUrl = 'some_url';

        $this->_helperStorage->expects($this->once())
            ->method('getRequestParams')
            ->will($this->returnValue($requestParams));

        $this->_urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/onInsert', $requestParams)
            ->will($this->returnValue($expectedUrl));

        $this->assertEquals($expectedUrl, $this->_filesContent->getOnInsertUrl());
    }

    /**
     * Data provider for requestParams
     * @return array
     */
    public function requestParamsProvider()
    {
        return array(
            'requestParams' => array(
                Mage_Theme_Helper_Storage::PARAM_THEME_ID     => 1,
                Mage_Theme_Helper_Storage::PARAM_CONTENT_TYPE => Mage_Theme_Model_Wysiwyg_Storage::TYPE_IMAGE,
                Mage_Theme_Helper_Storage::PARAM_NODE         => 'root'
            )
        );
    }

    public function testGetTargetElementId()
    {
        $expectedRequest = 'some_request';

        $this->_request->expects($this->once())
            ->method('getParam')
            ->with('target_element_id')
            ->will($this->returnValue($expectedRequest));

        $this->assertEquals($expectedRequest, $this->_filesContent->getTargetElementId());
    }

    public function testGetContentsUrl()
    {
        $expectedUrl = 'some_url';

        $expectedRequest = 'some_request';

        $requestParams = array(
            Mage_Theme_Helper_Storage::PARAM_THEME_ID     => 1,
            Mage_Theme_Helper_Storage::PARAM_CONTENT_TYPE => Mage_Theme_Model_Wysiwyg_Storage::TYPE_IMAGE,
            Mage_Theme_Helper_Storage::PARAM_NODE         => 'root'
        );

        $this->_urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/contents', array('type' => $expectedRequest) + $requestParams)
            ->will($this->returnValue($expectedUrl));

        $this->_request->expects($this->once())
            ->method('getParam')
            ->with('type')
            ->will($this->returnValue($expectedRequest));

        $this->_helperStorage->expects($this->once())
            ->method('getRequestParams')
            ->will($this->returnValue($requestParams));

        $this->assertEquals($expectedUrl, $this->_filesContent->getContentsUrl());
    }
}
