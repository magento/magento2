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
namespace Magento\Backend\Model\Config\Structure;

class AbstractElementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Structure\AbstractElement
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManager;

    protected function setUp()
    {
        $this->_storeManager = $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false);

        $this->_model = $this->getMockForAbstractClass(
            'Magento\Backend\Model\Config\Structure\AbstractElement',
            array($this->_storeManager)
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_storeManager);
    }

    public function testGetId()
    {
        $this->assertEquals('', $this->_model->getId());
        $this->_model->setData(array('id' => 'someId'), 'someScope');
        $this->assertEquals('someId', $this->_model->getId());
    }

    public function testGetLabelTranslatesLabel()
    {
        $this->assertEquals('', $this->_model->getLabel());
        $this->_model->setData(array('label' => 'some_label'), 'someScope');
        $this->assertEquals(__('some_label'), $this->_model->getLabel());
    }

    public function testGetCommentTranslatesComment()
    {
        $this->assertEquals('', $this->_model->getComment());
        $this->_model->setData(array('comment' => 'some_comment'), 'someScope');
        $this->assertEquals(__('some_comment'), $this->_model->getComment());
    }

    public function testGetFrontEndModel()
    {
        $this->_model->setData(array('frontend_model' => 'frontend_model_name'), 'store');
        $this->assertEquals('frontend_model_name', $this->_model->getFrontendModel());
    }

    public function testGetAttribute()
    {
        $this->_model->setData(
            array('id' => 'elementId', 'label' => 'Element Label', 'someAttribute' => 'Some attribute value'),
            'someScope'
        );
        $this->assertEquals('elementId', $this->_model->getAttribute('id'));
        $this->assertEquals('Element Label', $this->_model->getAttribute('label'));
        $this->assertEquals('Some attribute value', $this->_model->getAttribute('someAttribute'));
        $this->assertNull($this->_model->getAttribute('nonexistingAttribute'));
    }

    public function testIsVisibleReturnsTrueInSingleStoreModeForNonHiddenElements()
    {
        $this->_storeManager->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->_model->setData(
            array('showInDefault' => 1, 'showInStore' => 0, 'showInWebsite' => 0),
            \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT
        );
        $this->assertTrue($this->_model->isVisible());
    }

    public function testIsVisibleReturnsFalseInSingleStoreModeForHiddenElements()
    {
        $this->_storeManager->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->_model->setData(
            array('hide_in_single_store_mode' => 1, 'showInDefault' => 1, 'showInStore' => 0, 'showInWebsite' => 0),
            \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT
        );
        $this->assertFalse($this->_model->isVisible());
    }

    /**
     * Invisible elements is contains showInDefault="0" showInWebsite="0" showInStore="0"
     */
    public function testIsVisibleReturnsFalseInSingleStoreModeForInvisibleElements()
    {
        $this->_storeManager->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->_model->setData(
            array('showInDefault' => 0, 'showInStore' => 0, 'showInWebsite' => 0),
            \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT
        );
        $this->assertFalse($this->_model->isVisible());
    }

    /**
     * @param array $settings
     * @param string $scope
     * @dataProvider isVisibleReturnsTrueForProperScopesDataProvider
     */
    public function testIsVisibleReturnsTrueForProperScopes($settings, $scope)
    {
        $this->_model->setData($settings, $scope);
        $this->assertTrue($this->_model->isVisible());
    }

    public function isVisibleReturnsTrueForProperScopesDataProvider()
    {
        return array(
            array(
                array('showInDefault' => 1, 'showInStore' => 0, 'showInWebsite' => 0),
                \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT
            ),
            array(
                array('showInDefault' => 0, 'showInStore' => 1, 'showInWebsite' => 0),
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            array(
                array('showInDefault' => 0, 'showInStore' => 0, 'showInWebsite' => 1),
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
            )
        );
    }

    /**
     * @param array $settings
     * @param string $scope
     * @dataProvider isVisibleReturnsFalseForNonProperScopesDataProvider
     */
    public function testIsVisibleReturnsFalseForNonProperScopes($settings, $scope)
    {
        $this->_model->setData($settings, $scope);
        $this->assertFalse($this->_model->isVisible());
    }

    public function isVisibleReturnsFalseForNonProperScopesDataProvider()
    {
        return array(
            array(
                array('showInDefault' => 0, 'showInStore' => 1, 'showInWebsite' => 1),
                \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT
            ),
            array(
                array('showInDefault' => 1, 'showInStore' => 0, 'showInWebsite' => 1),
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            array(
                array('showInDefault' => 1, 'showInStore' => 1, 'showInWebsite' => 0),
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
            )
        );
    }

    public function testGetClass()
    {
        $this->assertEquals('', $this->_model->getClass());
        $this->_model->setData(array('class' => 'some_class'), 'store');
        $this->assertEquals('some_class', $this->_model->getClass());
    }

    public function testGetPathBuildsFullPath()
    {
        $this->_model->setData(array('path' => 'section/group', 'id' => 'fieldId'), 'scope');
        $this->assertEquals('section/group/prefix_fieldId', $this->_model->getPath('prefix_'));
    }
}
