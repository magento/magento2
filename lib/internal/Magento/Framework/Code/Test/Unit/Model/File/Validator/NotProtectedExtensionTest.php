<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit\Model\File\Validator;

use PHPUnit\Framework\TestCase;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Phrase;

class NotProtectedExtensionTest extends TestCase
{
    /**
     * @var NotProtectedExtension
     */
    protected $_model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $_scopeConfig;

    /**
     * @var string
     */
    protected $_protectedList = 'exe,php,jar';

    protected function setUp(): void
    {
        $this->_scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->_scopeConfig->expects(
            $this->atLeastOnce()
        )->method(
            'getValue'
        )->with(
            NotProtectedExtension::XML_PATH_PROTECTED_FILE_EXTENSIONS,
            ScopeInterface::SCOPE_STORE,
            null
        )->willReturn(
            $this->_protectedList
        );
        $this->_model = new NotProtectedExtension($this->_scopeConfig);
    }

    public function testGetProtectedFileExtensions()
    {
        $this->assertEquals($this->_protectedList, $this->_model->getProtectedFileExtensions());
    }

    public function testInitialization()
    {
        $property = new \ReflectionProperty(
            NotProtectedExtension::class,
            '_messageTemplates'
        );
        $property->setAccessible(true);
        $defaultMess = [
            'protectedExtension' => new Phrase('File with an extension "%value%" is protected and cannot be uploaded'),
        ];
        $this->assertEquals($defaultMess, $property->getValue($this->_model));

        $property = new \ReflectionProperty(
            NotProtectedExtension::class,
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
