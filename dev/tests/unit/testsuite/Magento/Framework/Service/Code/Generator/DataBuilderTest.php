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

namespace Magento\Framework\Service\Code\Generator;

use Magento\Framework\Code\Generator\Io;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Class BuilderTest
 */
class DataBuilderTest extends \PHPUnit_Framework_TestCase
{
    /*
     * The test is based on assumption that the classes will be injecting "DataBuilder" as dependency which will
     * indicate the compiler to identify and code generate based on ExtensibleSample implementations' interface
     */
    const SOURCE_CLASS_NAME = 'Magento\Framework\Service\Code\Generator\ExtensibleSample';
    const RESULT_CLASS_NAME = 'Magento\Framework\Service\Code\Generator\ExtensibleSampleDataBuilder';
    const GENERATOR_CLASS_NAME = 'Magento\Framework\Service\Code\Generator\DataBuilder';
    const OUTPUT_FILE_NAME = 'ExtensibleSampleDataBuilder.php';
    /**
     * @var Io | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ioObjectMock;

    /**
     * @var \Magento\Framework\Code\Generator\EntityAbstract
     */
    protected $generator;

    /**
     * @var \Magento\Framework\Autoload\IncludePath | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $autoloaderMock;

    /**
     * @var \Magento\Framework\Code\Generator\CodeGenerator\Zend | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $classGenerator;

    protected function setUp()
    {
        require_once __DIR__ . '/_files/ExtensibleSampleInterface.php';
        require_once __DIR__ . '/_files/ExtensibleSample.php';
        $this->ioObjectMock = $this->getMock(
            'Magento\Framework\Code\Generator\Io',
            [],
            [],
            '',
            false
        );
        $this->autoloaderMock = $this->getMock(
            'Magento\Framework\Autoload\IncludePath',
            [],
            [],
            '',
            false
        );
        $objectManager = new ObjectManager($this);
        $this->classGenerator = $objectManager->getObject('Magento\Framework\Code\Generator\CodeGenerator\Zend');
            $this->getMock(
            'Magento\Framework\Code\Generator\CodeGenerator\Zend',
            [],
            [],
            '',
            false
        );


        $this->generator = $objectManager->getObject(
            self::GENERATOR_CLASS_NAME,
            [
                'sourceClassName' => self::SOURCE_CLASS_NAME,
                'resultClassName' => self::RESULT_CLASS_NAME,
                'ioObject' => $this->ioObjectMock,
                'classGenerator' => $this->classGenerator,
                'autoLoader' => $this->autoloaderMock
            ]
        );
    }

    /**
     * generate repository name
     */
    public function testGenerate()
    {
        //$this->markTestIncomplete('Incomplete feature');
        $generatedCode = file_get_contents(__DIR__ . '/_files/ExtensibleSampleDataBuilder.txt');
        $sourceFileName = 'ExtensibleSample.php';
        $resultFileName = self::OUTPUT_FILE_NAME;

        //Mocking _validateData call
        $this->autoloaderMock->expects($this->at(0))
            ->method('getFile')
            ->with(self::SOURCE_CLASS_NAME)
            ->will($this->returnValue($sourceFileName));
        $this->autoloaderMock->expects($this->at(1))
            ->method('getFile')
            ->with(self::RESULT_CLASS_NAME)
            ->will($this->returnValue(false));

        $this->ioObjectMock->expects($this->once())
            ->method('makeGenerationDirectory')
            ->will($this->returnValue(true));
        $this->ioObjectMock->expects($this->once())
            ->method('makeResultFileDirectory')
            ->with(self::RESULT_CLASS_NAME)
            ->will($this->returnValue(true));
        $this->ioObjectMock->expects($this->once())
            ->method('fileExists')
            ->with($resultFileName)
            ->will($this->returnValue(false));

        //Mocking generation
        $this->ioObjectMock->expects($this->any())
            ->method('getResultFileName')
            ->with(self::RESULT_CLASS_NAME)
            ->will($this->returnValue($resultFileName));

        //Verify if the generated code is as expected
        $this->ioObjectMock->expects($this->once())
            ->method('writeResultFile')
            ->with($resultFileName, $generatedCode);

        $this->assertTrue($this->generator->generate());
    }
}
