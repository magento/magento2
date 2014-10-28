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
            array('get'),
            array(),
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
            array('getModuleDir'),
            array(),
            '',
            false
        );
        $this->_model = new \Magento\Email\Model\Template\Config($this->_dataStorage, $this->_moduleReader);
    }

    public function testGetAvailableTemplates()
    {
        $this->assertEquals(array('template_one', 'template_two'), $this->_model->getAvailableTemplates());
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
        return array(
            'label getter' => array('getTemplateLabel'),
            'type getter' => array('getTemplateType'),
            'module getter' => array('getTemplateModule'),
            'file getter' => array('getTemplateFilename')
        );
    }

    /**
     * @param string $getterMethod
     * @param string $expectedException
     * @param array $fixtureFields
     * @dataProvider getterMethodUnknownFieldDataProvider
     */
    public function testGetterMethodUnknownField($getterMethod, $expectedException, array $fixtureFields = array())
    {
        $this->setExpectedException('UnexpectedValueException', $expectedException);
        $dataStorage = $this->getMock('Magento\Email\Model\Template\Config\Data', array('get'), array(), '', false);
        $dataStorage->expects(
            $this->atLeastOnce()
        )->method(
            'get'
        )->will(
            $this->returnValue(array('fixture' => $fixtureFields))
        );
        $model = new \Magento\Email\Model\Template\Config($dataStorage, $this->_moduleReader);
        $model->{$getterMethod}('fixture');
    }

    public function getterMethodUnknownFieldDataProvider()
    {
        return array(
            'label getter' => array('getTemplateLabel', "Field 'label' is not defined for email template 'fixture'."),
            'type getter' => array('getTemplateType', "Field 'type' is not defined for email template 'fixture'."),
            'module getter' => array(
                'getTemplateModule',
                "Field 'module' is not defined for email template 'fixture'."
            ),
            'file getter, unknown module' => array(
                'getTemplateFilename',
                "Field 'module' is not defined for email template 'fixture'."
            ),
            'file getter, unknown file' => array(
                'getTemplateFilename',
                "Field 'file' is not defined for email template 'fixture'.",
                array('module' => 'Fixture_Module')
            )
        );
    }
}
