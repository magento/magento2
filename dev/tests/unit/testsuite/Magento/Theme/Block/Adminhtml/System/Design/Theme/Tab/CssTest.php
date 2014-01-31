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
 * @package     Magento_Theme
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Tab;

class CssTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css
     */
    protected $_model;

    /**
     * @var \Magento\ObjectManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_model = $this->getMock(
            'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css',
            array('_getCurrentTheme'),
            $this->_prepareModelArguments(),
            '',
            true
        );
    }

    /**
     * @return array
     */
    protected function _prepareModelArguments()
    {
        $this->_objectManager = $this->getMock('Magento\ObjectManager');

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments(
            'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css',
            array(
                'formFactory'      => $this->getMock('Magento\Data\FormFactory', array(), array(), '', false),
                'objectManager'   => $this->_objectManager,
                'uploaderService' => $this->getMock(
                    'Magento\Theme\Model\Uploader\Service', array(), array(), '', false
                ),
                'urlBuilder'      => $this->getMock('Magento\Backend\Model\Url', array(), array(), '', false)
            )
        );
        return $constructArguments;
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testGetUploadCssFileNote()
    {
        $method = self::getMethod('_getUploadCssFileNote');
        /** @var $sizeModel \Magento\File\Size */
        $sizeModel = $this->getMock('Magento\File\Size', null, array(), '', false);

        $this->_objectManager->expects($this->any())
            ->method('get')
            ->with('Magento\File\Size')
            ->will($this->returnValue($sizeModel));

        $result = $method->invokeArgs($this->_model, array());
        $expectedResult = 'Allowed file types *.css.<br />';
        $expectedResult .= 'This file will replace the current custom.css file and can\'t be more than 2 MB.<br />';
        $expectedResult .= sprintf(
            'Max file size to upload %sM',
            $sizeModel->getMaxFileSizeInMb()
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetAdditionalElementTypes()
    {
        $method = self::getMethod('_getAdditionalElementTypes');

        /** @var $configModel \Magento\App\ConfigInterface */
        $configModel = $this->getMock('Magento\App\ConfigInterface', array(), array(), '', false);

        $this->_objectManager->expects($this->any())
            ->method('get')
            ->with('Magento\App\ConfigInterface')
            ->will($this->returnValue($configModel));

        $result = $method->invokeArgs($this->_model, array());
        $expectedResult = array(
            'links' => 'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\Links',
            'css_file' => 'Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\File'
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetTabLabel()
    {
        $this->assertEquals('CSS Editor', $this->_model->getTabLabel());
    }

    /**
     * @param string $name
     * @return \ReflectionMethod
     */
    protected static function getMethod($name)
    {
        $class = new \ReflectionClass('Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
