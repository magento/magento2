<?php declare(strict_types=1);
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Model\View\Page\Config\ClientSideLessCompilation;

use Magento\Developer\Model\View\Page\Config\ClientSideLessCompilation\Renderer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Asset\PropertyGroup;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Page\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RendererTest extends TestCase
{
    /** @var MockObject|Renderer */
    private $model;

    /** @var  MockObject|GroupedCollection */
    private $assetCollectionMock;

    /** @var  MockObject|Repository */
    private $assetRepo;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetCollectionMock = $this->getMockBuilder(GroupedCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageConfigMock->expects($this->once())
            ->method('getAssetCollection')
            ->willReturn($this->assetCollectionMock);
        $this->assetRepo = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $overriddenMocks = [
            'assetRepo' => $this->assetRepo,
            'pageConfig' => $pageConfigMock
        ];

        $mocks = $objectManager->getConstructArguments(
            Renderer::class,
            $overriddenMocks
        );
        $this->model = $this->getMockBuilder(
            Renderer::class
        )
            ->setMethods(['renderAssetGroup'])
            ->setConstructorArgs($mocks)
            ->getMock();
    }

    /**
     * Test calls renderAssets as a way to execute renderLessJsScripts code
     */
    public function testRenderLessJsScripts()
    {
        // Stubs for renderAssets
        $propertyGroups = [
            $this->getMockBuilder(PropertyGroup::class)
                ->disableOriginalConstructor()
                ->getMock()
        ];
        $this->assetCollectionMock->expects($this->once())->method('getGroups')->willReturn($propertyGroups);

        // Stubs for renderLessJsScripts code
        $lessConfigFile = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lessMinFile = $this->getMockBuilder(File::class)
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
        $this->assetRepo->expects($this->exactly(2))->method('createAsset')->willReturnMap($assetMap);

        $resultGroups = "<script src=\"$lessConfigUrl\"></script>\n<script src=\"$lessMinUrl\"></script>\n";

        // Call method
        $this->assertSame($resultGroups, $this->model->renderAssets(['js' => '']));
    }
}
