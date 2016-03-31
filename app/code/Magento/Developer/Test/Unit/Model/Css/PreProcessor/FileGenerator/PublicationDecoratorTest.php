<?php
/***
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\Css\PreProcessor\FileGenerator;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Css\PreProcessor\File\Temporary;
use Magento\Developer\Model\Css\PreProcessor\FileGenerator\PublicationDecorator;

/**
 * Class PublicationDecoratorTest
 */
class PublicationDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Calls generate method to access protected method generateRelatedFile
     */
    public function testGenerateRelatedFile()
    {
        $filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileTemporaryMock = $this->getMockBuilder(Temporary::class)
            ->disableOriginalConstructor()
            ->getMock();

        $publisherMock = $this->getMockBuilder('Magento\Framework\App\View\Asset\Publisher')
            ->disableOriginalConstructor()
            ->getMock();
        $assetRepoMock = $this->getMockBuilder('Magento\Framework\View\Asset\Repository')
            ->disableOriginalConstructor()
            ->getMock();
        $relatedAssetMock = $this->getMockBuilder('Magento\Framework\View\Asset\File')
            ->disableOriginalConstructor()
            ->getMock();
        $importGeneratorMock = $this->getMockBuilder('Magento\Framework\Css\PreProcessor\Instruction\Import')
            ->disableOriginalConstructor()
            ->getMock();
        $localAssetMock = $this->getMockBuilder('Magento\Framework\View\Asset\LocalInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $relatedFileId = 'file_id';

        $relatedFiles = [[$relatedFileId, $localAssetMock]];

        $importGeneratorMock->expects(self::any())
            ->method('getRelatedFiles')
            ->will(self::onConsecutiveCalls($relatedFiles, []));

        $assetRepoMock->expects(self::any())
            ->method('createRelated')
            ->willReturn($relatedAssetMock);

        $publisherMock->expects(self::once())
            ->method('publish')
            ->with($relatedAssetMock);

        $model = new PublicationDecorator(
            $filesystemMock,
            $assetRepoMock,
            $fileTemporaryMock,
            $publisherMock,
            $scopeConfigMock,
            true
        );

        $model->generate($importGeneratorMock);
    }
}
