<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Model\File\Validator;

class NotProtectedExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\File\Validator\NotProtectedExtension
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * @var string
     */
    protected $_protectedList = 'exe,php,jar';

    protected function setUp()
    {
        $this->_scopeConfig = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_scopeConfig->expects(
            $this->atLeastOnce()
        )->method(
            'getValue'
        )->with(
            $this->equalTo(
                \Magento\Core\Model\File\Validator\NotProtectedExtension::XML_PATH_PROTECTED_FILE_EXTENSIONS
            ),
            $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            $this->equalTo(null)
        )->will(
            $this->returnValue($this->_protectedList)
        );
        $this->_model = new \Magento\Core\Model\File\Validator\NotProtectedExtension($this->_scopeConfig);
    }

    public function testGetProtectedFileExtensions()
    {
        $this->assertEquals($this->_protectedList, $this->_model->getProtectedFileExtensions());
    }

    public function testInitialization()
    {
        $property = new \ReflectionProperty(
            '\Magento\Core\Model\File\Validator\NotProtectedExtension',
            '_messageTemplates'
        );
        $property->setAccessible(true);
        $defaultMess = [
            'protectedExtension' => __('File with an extension "%value%" is protected and cannot be uploaded'),
        ];
        $this->assertEquals($defaultMess, $property->getValue($this->_model));

        $property = new \ReflectionProperty(
            '\Magento\Core\Model\File\Validator\NotProtectedExtension',
            '_protectedFileExtensions'
        );
        $property->setAccessible(true);
        $protectedList = ['exe', 'php', 'jar'];
        $this->assertEquals($protectedList, $property->getValue($this->_model));
    }

    public function testIsValid()
    {
        $this->assertTrue($this->_model->isValid('html'));
        $this->assertTrue($this->_model->isValid('jpg'));
        $this->assertFalse($this->_model->isValid('php'));
        $this->assertFalse($this->_model->isValid('jar'));
        $this->assertFalse($this->_model->isValid('exe'));
    }
}
