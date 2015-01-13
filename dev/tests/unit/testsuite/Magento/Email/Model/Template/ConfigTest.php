<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Template;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Email\Model\Template\Config
     */
    protected $_model;

    /**
     * @var \Magento\Email\Model\Template\Config\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataStorage;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleReader;

    protected function setUp()
    {
        $this->_dataStorage = $this->getMock(
            'Magento\Email\Model\Template\Config\Data',
            ['get'],
            [],
            '',
            false
        );
        $this->_dataStorage->expects(
            $this->any()
        )->method(
            'get'
        )->will(
            $this->returnValue(require __DIR__ . '/Config/_files/email_templates_merged.php')
        );
        $this->_moduleReader = $this->getMock(
            'Magento\Framework\Module\Dir\Reader',
            ['getModuleDir'],
            [],
            '',
            false
        );
        $this->_model = new \Magento\Email\Model\Template\Config($this->_dataStorage, $this->_moduleReader);
    }

    public function testGetAvailableTemplates()
    {
        $this->assertEquals(['template_one', 'template_two'], $this->_model->getAvailableTemplates());
    }

    public function testGetTemplateLabel()
    {
        $this->assertEquals('Template One', $this->_model->getTemplateLabel('template_one'));
    }

    public function testGetTemplateType()
    {
        $this->assertEquals('html', $this->_model->getTemplateType('template_one'));
    }

    public function testGetTemplateModule()
    {
        $this->assertEquals('Fixture_ModuleOne', $this->_model->getTemplateModule('template_one'));
    }

    public function testGetTemplateFilename()
    {
        $this->_moduleReader->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'view',
            'Fixture_ModuleOne'
        )->will(
            $this->returnValue('_files/Fixture/ModuleOne/view')
        );
        $actualResult = $this->_model->getTemplateFilename('template_one');
        $this->assertEquals('_files/Fixture/ModuleOne/view/email/one.html', $actualResult);
    }

    /**
     * @param string $getterMethod
     * @dataProvider getterMethodUnknownTemplateDataProvider
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Email template 'unknown' is not defined
     */
    public function testGetterMethodUnknownTemplate($getterMethod)
    {
        $this->_model->{$getterMethod}('unknown');
    }

    public function getterMethodUnknownTemplateDataProvider()
    {
        return [
            'label getter' => ['getTemplateLabel'],
            'type getter' => ['getTemplateType'],
            'module getter' => ['getTemplateModule'],
            'file getter' => ['getTemplateFilename']
        ];
    }

    /**
     * @param string $getterMethod
     * @param string $expectedException
     * @param array $fixtureFields
     * @dataProvider getterMethodUnknownFieldDataProvider
     */
    public function testGetterMethodUnknownField($getterMethod, $expectedException, array $fixtureFields = [])
    {
        $this->setExpectedException('UnexpectedValueException', $expectedException);
        $dataStorage = $this->getMock('Magento\Email\Model\Template\Config\Data', ['get'], [], '', false);
        $dataStorage->expects(
            $this->atLeastOnce()
        )->method(
            'get'
        )->will(
            $this->returnValue(['fixture' => $fixtureFields])
        );
        $model = new \Magento\Email\Model\Template\Config($dataStorage, $this->_moduleReader);
        $model->{$getterMethod}('fixture');
    }

    public function getterMethodUnknownFieldDataProvider()
    {
        return [
            'label getter' => ['getTemplateLabel', "Field 'label' is not defined for email template 'fixture'."],
            'type getter' => ['getTemplateType', "Field 'type' is not defined for email template 'fixture'."],
            'module getter' => [
                'getTemplateModule',
                "Field 'module' is not defined for email template 'fixture'.",
            ],
            'file getter, unknown module' => [
                'getTemplateFilename',
                "Field 'module' is not defined for email template 'fixture'.",
            ],
            'file getter, unknown file' => [
                'getTemplateFilename',
                "Field 'file' is not defined for email template 'fixture'.",
                ['module' => 'Fixture_Module'],
            ]
        ];
    }
}
