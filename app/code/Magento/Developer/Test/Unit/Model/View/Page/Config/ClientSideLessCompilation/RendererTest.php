<?php
/***
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Model\View\Page\Config\ClientSideLessCompilation;

use Magento\Developer\Model\View\Page\Config\ClientSideLessCompilation\Renderer;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject | Renderer */
    private $model;

    /** @var  \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\View\Asset\GroupedCollection */
    private $assetCollectionMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\View\Asset\Repository */
    private $assetRepo;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetCollectionMock = $this->getMockBuilder(\Magento\Framework\View\Asset\GroupedCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageConfigMock->expects($this->once())
            ->method('getAssetCollection')
            ->willReturn($this->assetCollectionMock);
        $this->assetRepo = $this->getMockBuilder(\Magento\Framework\View\Asset\Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $overriddenMocks = [
            'assetRepo' => $this->assetRepo,
            'pageConfig' => $pageConfigMock
        ];

        $mocks = $objectManager->getConstructArguments(
            \Magento\Developer\Model\View\Page\Config\ClientSideLessCompilation\Renderer::class,
            $overriddenMocks
        );
        $this->model = $this->getMock(
            \Magento\Developer\Model\View\Page\Config\ClientSideLessCompilation\Renderer::class,
            ['renderAssetGroup'],
            $mocks
        );
    }

    /**
     * Test calls renderAssets as a way to execute renderLessJsScripts code
     */
    public function testRenderLessJsScripts()
    {
        // Stubs for renderAssets
        $propertyGroups = [
            $this->getMockBuilder(\Magento\Framework\View\Asset\PropertyGroup::class)
                ->disableOriginalConstructor()
                ->getMock()
        ];
        $this->assetCollectionMock->expects($this->once())->method('getGroups')->willReturn($propertyGroups);

        // Stubs for renderLessJsScripts code
        $lessConfigFile = $this->getMockBuilder(\Magento\Framework\View\Asset\File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lessMinFile = $this->getMockBuilder(\Magento\Framework\View\Asset\File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lessConfigUrl = 'less/config/url.css';
        $lessMinUrl = 'less/min/url.css';
        $lessConfigFile->expects($this->once())->method('getUrl')->willReturn($lessConfigUrl);
        $lessMinFile->expects($this->once())->method('getUrl')->willReturn($lessMinUrl);

        $assetMap = [
            ['less/config.less.js', [], $lessConfigFile],
            ['less/less.min.js', [], $lessMinFile]
        ];
        $this->assetRepo->expects($this->exactly(2))->method('createAsset')->will($this->returnValueMap($assetMap));

        $resultGroups = "<script src=\"$lessConfigUrl\"></script>\n<script src=\"$lessMinUrl\"></script>\n";

        // Call method
        $this->assertSame($resultGroups, $this->model->renderAssets(['js' => '']));
    }
}
