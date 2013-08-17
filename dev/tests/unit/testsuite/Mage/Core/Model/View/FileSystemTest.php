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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test for view filesystem model
 */
class Mage_Core_Model_View_FileSystemTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_View_FileSystem|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var Mage_Core_Model_Design_FileResolution_StrategyPool|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_strategyPool;

    /**
     * @var Mage_Core_Model_View_Service|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_viewService;


    protected function setUp()
    {
        $this->_strategyPool = $this->getMock('Mage_Core_Model_Design_FileResolution_StrategyPool', array(),
            array(), '', false
        );
        $this->_viewService = $this->getMock('Mage_Core_Model_View_Service',
            array('extractScope', 'updateDesignParams'), array(), '', false
        );

        $this->_model = new Mage_Core_Model_View_FileSystem($this->_strategyPool, $this->_viewService);
    }

    public function testGetFilename()
    {
        $params = array(
            'area'       => 'some_area',
            'themeModel' => $this->getMock('Mage_Core_Model_Theme', array(), array(), '', false, false),
            'module'     => 'Some_Module'   //It should be set in Mage_Core_Model_View_Service::extractScope
                                            // but PHPUnit has problems with passing arguments by reference
        );
        $file = 'Some_Module::some_file.ext';
        $expected = 'path/to/some_file.ext';

        $strategyMock = $this->getMock('Mage_Core_Model_Design_FileResolution_Strategy_FileInterface');
        $strategyMock->expects($this->once())
            ->method('getFile')
            ->with($params['area'], $params['themeModel'], 'some_file.ext', 'Some_Module')
            ->will($this->returnValue($expected));

        $this->_strategyPool->expects($this->once())
            ->method('getFileStrategy')
            ->with(false)
            ->will($this->returnValue($strategyMock));

        $this->_viewService->expects($this->any())
            ->method('extractScope')
            ->with($file, $params)
            ->will($this->returnValue('some_file.ext'));

        $actual = $this->_model->getFilename($file, $params);
        $this->assertEquals($expected, $actual);
    }

    public function testGetLocaleFileName()
    {
        $params = array(
            'area' => 'some_area',
            'themeModel' => $this->getMock('Mage_Core_Model_Theme', array(), array(), '', false, false),
            'locale' => 'some_locale'
        );
        $file = 'some_file.ext';
        $expected = 'path/to/some_file.ext';

        $strategyMock = $this->getMock('Mage_Core_Model_Design_FileResolution_Strategy_LocaleInterface');
        $strategyMock->expects($this->once())
            ->method('getLocaleFile')
            ->with($params['area'], $params['themeModel'], $params['locale'], 'some_file.ext')
            ->will($this->returnValue($expected));

        $this->_strategyPool->expects($this->once())
            ->method('getLocaleStrategy')
            ->with(false)
            ->will($this->returnValue($strategyMock));

        $actual = $this->_model->getLocaleFileName($file, $params);
        $this->assertEquals($expected, $actual);
    }

    public function testGetViewFile()
    {
        $params = array(
            'area'       => 'some_area',
            'themeModel' => $this->getMock('Mage_Core_Model_Theme', array(), array(), '', false, false),
            'locale'     => 'some_locale',
            'module'     => 'Some_Module'   //It should be set in Mage_Core_Model_View_Service::extractScope
                                            // but PHPUnit has problems with passing arguments by reference
        );
        $file = 'Some_Module::some_file.ext';
        $expected = 'path/to/some_file.ext';

        $strategyMock = $this->getMock('Mage_Core_Model_Design_FileResolution_Strategy_ViewInterface');
        $strategyMock->expects($this->once())
            ->method('getViewFile')
            ->with($params['area'], $params['themeModel'], $params['locale'], 'some_file.ext', 'Some_Module')
            ->will($this->returnValue($expected));

        $this->_strategyPool->expects($this->once())
            ->method('getViewStrategy')
            ->with(false)
            ->will($this->returnValue($strategyMock));

        $this->_viewService->expects($this->any())
            ->method('extractScope')
            ->with($file, $params)
            ->will($this->returnValue('some_file.ext'));

        $actual = $this->_model->getViewFile($file, $params);
        $this->assertEquals($expected, $actual);
    }
}
