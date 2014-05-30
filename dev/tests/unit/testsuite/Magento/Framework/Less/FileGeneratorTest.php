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

namespace Magento\Framework\Less;

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
        $filesystem = $this->getMock('\Magento\Framework\App\Filesystem', array(), array(), '', false);
        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(\Magento\Framework\App\Filesystem::VAR_DIR)
            ->will($this->returnValue($this->tmpDirectory));
        $this->assetRepo = $this->getMock('\Magento\Framework\View\Asset\Repository', array(), array(), '', false);
        $this->magentoImport = $this->getMock(
            '\Magento\Framework\Less\PreProcessor\Instruction\MagentoImport', array(), array(), '', false
        );
        $this->import = $this->getMock(
            '\Magento\Framework\Less\PreProcessor\Instruction\Import', array(), array(), '', false
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

        $asset = $this->getMock('\Magento\Framework\View\Asset\File', array(), array(), '', false);
        $asset->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('some/file.css'));
        $chain = new \Magento\Framework\View\Asset\PreProcessor\Chain($asset, $originalContent, 'less');

        $this->magentoImport->expects($this->once())
            ->method('process')
            ->with($chain)
        ;
        $this->import->expects($this->once())
            ->method('process')
            ->with($chain)
        ;

        $relatedAssetOne = $this->getMock('\Magento\Framework\View\Asset\File', array(), array(), '', false);
        $relatedAssetOne->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('related/file_one.css'));
        $relatedAssetOne->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue("content of 'related/file_one.css'"));
        $relatedAssetTwo = $this->getMock('\Magento\Framework\View\Asset\File', array(), array(), '', false);
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
