<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Less\Test\Unit;

class FileGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Less\PreProcessor\Instruction\Import|\PHPUnit_Framework_MockObject_MockObject
     */
    private $import;

    /**
     * @var \Magento\Framework\Less\PreProcessor\Instruction\MagentoImport|\PHPUnit_Framework_MockObject_MockObject
     */
    private $magentoImport;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tmpDirectory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rootDirectory;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\Less\FileGenerator
     */
    private $object;

    /**
     * @var \Magento\Framework\Less\FileGenerator\RelatedGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $relatedGenerator;

    /**
     * @var \Magento\Framework\Less\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \Magento\Framework\Less\File\Temporary|\PHPUnit_Framework_MockObject_MockObject
     */
    private $temporaryFile;

    protected function setUp()
    {
        $this->tmpDirectory = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->rootDirectory = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->rootDirectory->expects($this->any())
            ->method('getRelativePath')
            ->will($this->returnArgument(0));
        $this->rootDirectory->expects($this->any())
            ->method('readFile')
            ->will(
                $this->returnCallback(
                    function ($file) {
                        return "content of '$file'";
                    }
                )
            );
        $filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($this->tmpDirectory));
        $this->assetRepo = $this->getMock('\Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->magentoImport = $this->getMock(
            'Magento\Framework\Less\PreProcessor\Instruction\MagentoImport',
            [],
            [],
            '',
            false
        );
        $this->import = $this->getMock(
            'Magento\Framework\Less\PreProcessor\Instruction\Import',
            [],
            [],
            '',
            false
        );

        $assetSource = $this->getMock(
            'Magento\Framework\View\Asset\Source',
            [],
            [],
            '',
            false
        );

        $this->relatedGenerator = $this->getMockBuilder('Magento\Framework\Less\FileGenerator\RelatedGenerator')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->config = $this->getMockBuilder('Magento\Framework\Less\Config')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->temporaryFile = $this->getMockBuilder('Magento\Framework\Less\File\Temporary')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->object = new \Magento\Framework\Less\FileGenerator(
            $filesystem,
            $this->assetRepo,
            $this->magentoImport,
            $this->import,
            $assetSource,
            $this->relatedGenerator,
            $this->config,
            $this->temporaryFile
        );
    }

    public function testGenerateLessFileTree()
    {
        $lessDirectory = 'path/to/less';
        $expectedContent = 'updated content';
        $expectedRelativePath = 'some/file.less';
        $expectedPath = $lessDirectory . '/some/file.less';


        $asset = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $chain = $this->getMock('Magento\Framework\View\Asset\PreProcessor\Chain', [], [], '', false);

        $this->config->expects($this->any())
            ->method('getLessDirectory')
            ->willReturn($lessDirectory);
        $this->tmpDirectory->expects($this->once())
            ->method('isExist')
            ->willReturn(true);

        $this->magentoImport->expects($this->once())
            ->method('process')
            ->with($chain);
        $this->import->expects($this->once())
            ->method('process')
            ->with($chain);
        $this->relatedGenerator->expects($this->once())
            ->method('generate')
            ->with($this->import);

        $asset->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('some/file.css'));
        $chain->expects($this->once())
            ->method('getContent')
            ->willReturn($expectedContent);
        $chain->expects($this->once())
            ->method('getAsset')
            ->willReturn($asset);

        $this->temporaryFile->expects($this->once())
            ->method('createFile')
            ->with(
                $expectedRelativePath,
                $expectedContent
            )
            ->willReturn($expectedPath);

        $this->assertSame($expectedPath, $this->object->generateFileTree($chain));
    }
}
