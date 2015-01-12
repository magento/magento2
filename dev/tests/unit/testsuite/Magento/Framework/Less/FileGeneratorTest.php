<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Less;

use Magento\Framework\App\Filesystem\DirectoryList;

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

    protected function setUp()
    {
        $this->tmpDirectory = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->rootDirectory = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->rootDirectory->expects($this->any())
            ->method('getRelativePath')
            ->will($this->returnArgument(0));
        $this->rootDirectory->expects($this->any())
            ->method('readFile')
            ->will($this->returnCallback(function ($file) {
                return "content of '$file'";
            }));
        $filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
            ->will($this->returnValue($this->tmpDirectory));
        $this->assetRepo = $this->getMock('\Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->magentoImport = $this->getMock(
            '\Magento\Framework\Less\PreProcessor\Instruction\MagentoImport', [], [], '', false
        );
        $this->import = $this->getMock(
            '\Magento\Framework\Less\PreProcessor\Instruction\Import', [], [], '', false
        );
        $this->object = new \Magento\Framework\Less\FileGenerator(
            $filesystem, $this->assetRepo, $this->magentoImport, $this->import
        );
    }

    public function testGenerateLessFileTree()
    {
        $originalContent = 'original content';
        $expectedContent = 'updated content';
        $expectedRelativePath = 'view_preprocessed/less/some/file.less';
        $expectedPath = '/var/view_preprocessed/less/some/file.less';

        $asset = $this->getMock('\Magento\Framework\View\Asset\File', [], [], '', false);
        $asset->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('some/file.css'));
        $chain = new \Magento\Framework\View\Asset\PreProcessor\Chain($asset, $originalContent, 'less');

        $this->magentoImport->expects($this->once())
            ->method('process')
            ->with($chain);
        $this->import->expects($this->once())
            ->method('process')
            ->with($chain);

        $relatedAssetOne = $this->getMock('\Magento\Framework\View\Asset\File', [], [], '', false);
        $relatedAssetOne->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('related/file_one.css'));
        $relatedAssetOne->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue("content of 'related/file_one.css'"));
        $relatedAssetTwo = $this->getMock('\Magento\Framework\View\Asset\File', [], [], '', false);
        $relatedAssetTwo->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('related/file_two.css'));
        $relatedAssetTwo->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue("content of 'related/file_two.css'"));
        $assetsMap = [
            ['related/file_one.css', $asset, $relatedAssetOne],
            ['related/file_two.css', $asset, $relatedAssetTwo],
        ];
        $this->assetRepo->expects($this->any())
            ->method('createRelated')
            ->will($this->returnValueMap($assetsMap));

        $relatedFilesOne = [['related/file_one.css', $asset]];
        $this->import->expects($this->at(1))
            ->method('getRelatedFiles')
            ->will($this->returnValue($relatedFilesOne));
        $relatedFilesTwo = [['related/file_two.css', $asset]];
        $this->import->expects($this->at(3))
            ->method('getRelatedFiles')
            ->will($this->returnValue($relatedFilesTwo));
        $this->import->expects($this->at(5))
            ->method('getRelatedFiles')
            ->will($this->returnValue([]));

        $writeMap = [
            [$expectedRelativePath, $expectedContent],
            ['related/file_one.css', "content of 'related/file_one.css'"],
            ['related/file_two.css', "content of 'related/file_two.css'"],
        ];
        $pathsMap = [
            [$expectedRelativePath, $expectedPath],
            ['related/file_one.css', '/var/view_preprocessed/less/related/file_one.css'],
            ['related/file_two.css', '/var/view_preprocessed/less/related/file_two.css'],
        ];
        $this->tmpDirectory->expects($this->any())
            ->method('writeFile')
            ->will($this->returnValueMap($writeMap));
        $this->tmpDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnValueMap($pathsMap));

        $actual = $this->object->generateLessFileTree($chain);
        $this->assertSame($expectedPath, $actual);
    }
}
