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
 * @package     Magento_Di
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Di_Generator_EntityTestAbstract extends PHPUnit_Framework_TestCase
{
    /**#@+
     * Source and result class parameters
     */
    const SOURCE_CLASS = 'ClassName';
    const RESULT_CLASS = 'ClassNameFactory';
    const RESULT_FILE  = 'ClassNameFactory.php';
    /**#@-*/

    /**
     * Generated code
     */
    const CODE = "a = 1;";

    protected static $_expectedMethods = array();

    /**
     * @return Magento_Di_Generator_Io|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getIoObjectMock()
    {
        $ioObjectMock = $this->getMock('Magento_Di_Generator_Io',
            array('getResultFileName', 'makeGenerationDirectory', 'makeResultFileDirectory', 'fileExists',
                'writeResultFile'
            ), array(), '', false
        );
        $ioObjectMock->expects($this->any())
            ->method('getResultFileName')
            ->will($this->returnValue(static::RESULT_FILE));
        $ioObjectMock->expects($this->any())
            ->method('makeGenerationDirectory')
            ->will($this->returnValue(true));
        $ioObjectMock->expects($this->any())
            ->method('makeResultFileDirectory')
            ->will($this->returnValue(true));
        $ioObjectMock->expects($this->any())
            ->method('fileExists')
            ->will($this->returnValue(false));
        $ioObjectMock->expects($this->once())
            ->method('writeResultFile')
            ->with(static::RESULT_FILE, static::CODE);

        return $ioObjectMock;
    }

    /**
     * @return Magento_Autoload_IncludePath|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getAutoloaderMock()
    {
        $autoLoaderMock = $this->getMock('Magento_Autoload_IncludePath', array('getFile'), array(), '', false);
        $autoLoaderMock->staticExpects($this->at(0))
            ->method('getFile')
            ->with(static::SOURCE_CLASS)
            ->will($this->returnValue(true));
        $autoLoaderMock->staticExpects($this->at(1))
            ->method('getFile')
            ->with(static::RESULT_CLASS)
            ->will($this->returnValue(false));

        return $autoLoaderMock;
    }

    /**
     * @param array $methodNames
     * @return Magento_Di_Generator_CodeGenerator_Zend|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getCodeGeneratorMock(array $methodNames)
    {
        $codeGeneratorMock
            = $this->getMock('Magento_Di_Generator_CodeGenerator_Zend', $methodNames, array(), '', false);
        $codeGeneratorMock->expects($this->once())
            ->method('setName')
            ->with(static::RESULT_CLASS)
            ->will($this->returnSelf());
        $codeGeneratorMock->expects($this->once())
            ->method('addProperties')
            ->will($this->returnSelf());
        $codeGeneratorMock->expects($this->once())
            ->method('addMethods')
            ->with(static::$_expectedMethods)
            ->will($this->returnSelf());
        $codeGeneratorMock->expects($this->once())
            ->method('setClassDocBlock')
            ->with($this->isType('array'))
            ->will($this->returnSelf());
        $codeGeneratorMock->expects($this->once())
            ->method('generate')
            ->will($this->returnValue(self::CODE));

        return $codeGeneratorMock;
    }
}
