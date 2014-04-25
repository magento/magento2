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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Interception\Code\Generator;

class InterceptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject
     */
    protected $ioObjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject
     */
    protected $classGeneratorMock;

    /**
     * @var \PHPUnit_Framework_MockObject
     */
    protected $autoloaderMock;

    protected function setUp()
    {
        $this->ioObjectMock = $this->getMock('\Magento\Framework\Code\Generator\Io', array(), array(), '', false);
        $this->classGeneratorMock = $this->getMock(
            '\Magento\Framework\Code\Generator\CodeGenerator\CodeGeneratorInterface',
            array(),
            array(),
            '',
            false
        );
        $this->autoloaderMock = $this->getMock('\Magento\Framework\Autoload\IncludePath', array(), array(), '', false);
    }

    public function testGetDefaultResultClassName()
    {
        // resultClassName should be stdClass_Interceptor
        $model = $this->getMock('\Magento\Framework\Interception\Code\Generator\Interceptor',
            array('_validateData'),
            array('Exception', null, $this->ioObjectMock, $this->classGeneratorMock, $this->autoloaderMock)
        );

        $this->classGeneratorMock->expects($this->once())->method('setName')
            ->will($this->returnValue($this->classGeneratorMock));
        $this->classGeneratorMock->expects($this->once())->method('addProperties')
            ->will($this->returnValue($this->classGeneratorMock));
        $this->classGeneratorMock->expects($this->once())->method('addMethods')
            ->will($this->returnValue($this->classGeneratorMock));
        $this->classGeneratorMock->expects($this->once())->method('setClassDocBlock')
            ->will($this->returnValue($this->classGeneratorMock));
        $this->classGeneratorMock->expects($this->once())->method('generate')
            ->will($this->returnValue('source code example'));
        $model->expects($this->once())->method('_validateData')->will($this->returnValue(true));
        $this->ioObjectMock->expects($this->any())->method('getResultFileName')->with('Exception_Interceptor');
        $this->assertTrue($model->generate());
    }
}
