<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Block\Cache;

class AdditonalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Cache\Additional
     */
    private $additonalBlock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    protected function setUp()
    {
        $this->urlBuilderMock = $this->getMock('Magento\Framework\UrlInterface');

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $context = $objectHelper->getObject(
            'Magento\Backend\Block\Template\Context',
            ['urlBuilder' => $this->urlBuilderMock]
        );

        $this->additonalBlock = $objectHelper->getObject(
            'Magento\Backend\Block\Cache\Additional',
            ['context' => $context,]
        );
    }

    public function testGetCleanImagesUrl()
    {
        $expectedUrl = 'cleanImagesUrl';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/cleanImages')
            ->will($this->returnValue($expectedUrl));
        $this->assertEquals($expectedUrl, $this->additonalBlock->getCleanImagesUrl());
    }

    public function testGetCleanMediaUrl()
    {
        $expectedUrl = 'cleanMediaUrl';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/cleanMedia')
            ->will($this->returnValue($expectedUrl));
        $this->assertEquals($expectedUrl, $this->additonalBlock->getCleanMediaUrl());
    }

    public function testGetCleanStaticFiles()
    {
        $expectedUrl = 'cleanStaticFilesUrl';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/cleanStaticFiles')
            ->will($this->returnValue($expectedUrl));
        $this->assertEquals($expectedUrl, $this->additonalBlock->getCleanStaticFilesUrl());
    }
}
