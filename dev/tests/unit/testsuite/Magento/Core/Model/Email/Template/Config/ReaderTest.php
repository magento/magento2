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
namespace Magento\Core\Model\Email\Template\Config;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Email\Template\Config\Reader
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Attribute\Config\Converter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converter;

    /**
     * @var \Magento\App\Module\Dir\ReverseResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleDirResolver;

    protected function setUp()
    {
        $fileResolver = $this->getMock('Magento\Config\FileResolverInterface');
        $fileResolver
            ->expects($this->once())
            ->method('get')
            ->with('email_templates.xml', 'scope')
            ->will($this->returnValue(array(
                __DIR__ . '/_files/Fixture/ModuleOne/etc/email_templates_one.xml',
                __DIR__ . '/_files/Fixture/ModuleTwo/etc/email_templates_two.xml',
            )))
        ;

        $this->_converter = $this->getMock('Magento\Core\Model\Email\Template\Config\Converter', array('convert'));

        $moduleReader = $this->getMock(
            'Magento\Core\Model\Config\Modules\Reader', array('getModuleDir'), array(), '', false
        );
        $moduleReader
            ->expects($this->once())
            ->method('getModuleDir')->with('etc', 'Magento_Core')
            ->will($this->returnValue('stub'))
        ;
        $schemaLocator = new \Magento\Core\Model\Email\Template\Config\SchemaLocator($moduleReader);

        $validationState = $this->getMock('Magento\Config\ValidationStateInterface');
        $validationState->expects($this->once())->method('isValidated')->will($this->returnValue(false));

        $this->_moduleDirResolver = $this->getMock(
            'Magento\App\Module\Dir\ReverseResolver', array(), array(), '', false
        );

        $this->_model = new \Magento\Core\Model\Email\Template\Config\Reader(
            $fileResolver,
            $this->_converter,
            $schemaLocator,
            $validationState,
            $this->_moduleDirResolver
        );
    }

    public function testRead()
    {
        $this->_moduleDirResolver
            ->expects($this->at(0))
            ->method('getModuleName')
            ->with(__DIR__ . '/_files/Fixture/ModuleOne/etc/email_templates_one.xml')
            ->will($this->returnValue('Fixture_ModuleOne'))
        ;
        $this->_moduleDirResolver
            ->expects($this->at(1))
            ->method('getModuleName')
            ->with(__DIR__ . '/_files/Fixture/ModuleTwo/etc/email_templates_two.xml')
            ->will($this->returnValue('Fixture_ModuleTwo'))
        ;
        $constraint = function (\DOMDOcument $actual) {
            try {
                $expected = __DIR__ . '/_files/email_templates_merged.xml';
                \PHPUnit_Framework_Assert::assertXmlStringEqualsXmlFile($expected, $actual->saveXML());
                return true;
            } catch (\PHPUnit_Framework_AssertionFailedError $e) {
                return false;
            }
        };
        $expectedResult = new \stdClass();
        $this->_converter
            ->expects($this->once())
            ->method('convert')
            ->with($this->callback($constraint))
            ->will($this->returnValue($expectedResult))
        ;
        $this->assertSame($expectedResult, $this->_model->read('scope'));
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Unable to determine a module
     */
    public function testReadUnknownModule()
    {
        $this->_moduleDirResolver->expects($this->once())->method('getModuleName')->will($this->returnValue(null));
        $this->_converter->expects($this->never())->method('convert');
        $this->_model->read('scope');
    }
}
