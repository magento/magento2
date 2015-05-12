<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Model\Less\FileGenerator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PublicationDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Calls generate method to access protected method generateRelatedFile
     */
    public function testGenerateRelatedFile()
    {
        $publisherMock = $this->getMockBuilder('Magento\Framework\App\View\Asset\Publisher')
            ->disableOriginalConstructor()
            ->getMock();
        $assetRepoMock = $this->getMockBuilder('Magento\Framework\View\Asset\Repository')
            ->disableOriginalConstructor()
            ->getMock();
        $relatedAssetMock = $this->getMockBuilder('Magento\Framework\View\Asset\File')
            ->disableOriginalConstructor()
            ->getMock();
        $importGeneratorMock = $this->getMockBuilder('Magento\Framework\Less\PreProcessor\Instruction\Import')
            ->disableOriginalConstructor()
            ->getMock();
        $localAssetMock = $this->getMockBuilder('Magento\Framework\View\Asset\LocalInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $relatedFileId = 'file_id';

        $relatedFiles = [[$relatedFileId, $localAssetMock]];
        $importGeneratorMock->expects($this->any())
            ->method('getRelatedFiles')
            ->will($this->onConsecutiveCalls($relatedFiles, []));
        $assetRepoMock->expects($this->any())
            ->method('createRelated')
            ->willReturn($relatedAssetMock);
        $publisherMock->expects($this->once())
            ->method('publish')
            ->with($relatedAssetMock);

        $args = [
            'assetRepo' => $assetRepoMock,
            'publisher' => $publisherMock
        ];

        $model = (new ObjectManager($this))->getObject(
            'Magento\Developer\Model\Less\FileGenerator\PublicationDecorator',
            $args
        );

        $model->generate($importGeneratorMock);
    }
}
