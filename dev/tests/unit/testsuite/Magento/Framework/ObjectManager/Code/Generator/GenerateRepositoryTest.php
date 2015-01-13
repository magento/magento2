<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        /** @var \PHPUnit_Framework_MockObject_MockObject $model */
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
                null,
                $this->getMock('Magento\Framework\Filesystem\FileResolver')
            ]
        );

        $this->ioObjectMock->expects($this->once())
            ->method('getResultFileName')
            ->with('\Magento\Framework\ObjectManager\Code\Generator\SampleRepository')
            ->willReturn('SampleRepository.php');

        $model->expects($this->once())->method('_validateData')->willReturn(true);
        $this->assertEquals('SampleRepository.php', $model->generate());
    }

    /**
     * test protected _validateData()
     */
    public function testValidateData()
    {
        $sourceClassName = 'Magento_Module_Controller_Index';
        $resultClassName = 'Magento_Module_Controller';

        $repository = new Repository();
        $repository->init($sourceClassName, $resultClassName);
        $this->assertFalse($repository->generate());
    }
}
