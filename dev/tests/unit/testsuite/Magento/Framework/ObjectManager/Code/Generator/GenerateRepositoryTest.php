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
namespace Magento\Framework\ObjectManager\Code\Generator;

/**
 * Class RepositoryTest
 */
class GenerateRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ioObjectMock;

    /**
     * test setUp
     */
    protected function setUp()
    {
        $this->ioObjectMock = $this->getMock(
            '\Magento\Framework\Code\Generator\Io',
            [],
            [],
            '',
            false
        );
    }

    /**
     * generate repository name
     */
    public function testGenerate()
    {
        require_once __DIR__ . '/_files/Sample.php';
        $model = $this->getMock(
            'Magento\Framework\ObjectManager\Code\Generator\Repository',
            [
                '_validateData'
            ],
            [
                '\Magento\Framework\ObjectManager\Code\Generator\Sample',
                null,
                $this->ioObjectMock,
                null,
                null
            ]
        );
        $sampleRepositoryCode = file_get_contents(__DIR__ . '/_files/SampleRepository.txt');

        $this->ioObjectMock->expects($this->once())
            ->method('getResultFileName')
            ->with('\Magento\Framework\ObjectManager\Code\Generator\SampleRepository')
            ->will($this->returnValue('SampleRepository.php'));
        $this->ioObjectMock->expects($this->once())
            ->method('writeResultFile')
            ->with(
                $this->equalTo('SampleRepository.php'),
                $this->equalTo($sampleRepositoryCode)
            );

        $model->expects($this->once())->method('_validateData')->will($this->returnValue(true));
        $this->assertTrue($model->generate());
    }

    /**
     * test protected _validateData()
     */
    public function testValidateData()
    {
        $sourceClassName = 'Magento_Module_Controller_Index';
        $resultClassName = 'Magento_Module_Controller';

        $includePathMock = $this->getMockBuilder('Magento\Framework\Autoload\IncludePath')
            ->disableOriginalConstructor()
            ->setMethods(['getFile'])
            ->getMock();
        $includePathMock->expects($this->at(0))
            ->method('getFile')
            ->with($sourceClassName)
            ->will($this->returnValue(true));
        $includePathMock->expects($this->at(1))
            ->method('getFile')
            ->with($resultClassName)
            ->will($this->returnValue(false));

        $repository = new Repository(
            null, null, null, null, $includePathMock
        );
        $repository->init($sourceClassName, $resultClassName);
        $this->assertFalse($repository->generate());
    }
}
