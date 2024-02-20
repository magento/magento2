<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Plugin;

use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Framework\App\Request\Http;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Csp\Plugin\AddDefaultPropertiesToGroupPlugin;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\GroupedCollection;

/**
 * Test for class Magento\Csp\Plugin\AddDefaultPropertiesToGroupPlugin
 *
 */
class AddDefaultPropertiesToGroupPluginTest extends TestCase
{

    /**
     * @var MockObject
     */
    private MockObject $requestMock;

    /**
     * @var MockObject
     */
    private MockObject $assetInterfaceMock;

    /**
     * @var MockObject
     */
    private MockObject $integrityRepositoryMock;

    /**
     * @var array $controllerActions
     */
    private array $controllerActions;

    /**
     * @var AddDefaultPropertiesToGroupPlugin
     */
    private AddDefaultPropertiesToGroupPlugin $plugin;

    /**
     * Initialize Dependencies
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->integrityRepositoryMock = $this->getMockBuilder(SubresourceIntegrityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByUrl'])
            ->getMock();
        $this->assetInterfaceMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrl', 'getContentType'])
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFullActionName'])
            ->getMock();
        $this->controllerActions = ['checkout_index_index', 'sales_order_create'];
        $this->plugin = new AddDefaultPropertiesToGroupPlugin(
            $this->requestMock,
            $this->integrityRepositoryMock,
            $this->controllerActions
        );
    }

    /**
     * Test for plugin with Js assets
     *
     * @return void
     */
    public function testBeforeGetFilteredProperties(): void
    {
        $groupedCollectionMock = $this->getMockBuilder(GroupedCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $url = 'https://magento.test/static/version1708401324/frontend/Magento/luma/en_US/jquery.js';

        $data = new SubresourceIntegrity(
            [
                'hash' => 'testhash',
                'url' => $url
            ]
        );
        $properties['attributes']['integrity'] = $data->getHash();
        $properties['attributes']['crossorigin'] = 'anonymous';
        $expected = [$this->assetInterfaceMock, $properties];
        $this->assetInterfaceMock->expects($this->once())->method('getContentType')->willReturn('js');
        $this->assetInterfaceMock->expects($this->once())->method('getUrl')->willReturn($url);
        $this->integrityRepositoryMock->expects($this->once())->method('getByUrl')->with($url)->willReturn($data);
        $this->requestMock->expects($this->once())->method('getFullActionName')->willReturn('sales_order_create');
        $this->assertEquals($expected,
            $this->plugin->beforeGetFilteredProperties($groupedCollectionMock, $this->assetInterfaceMock
            )
        );
    }

    /**
     * Test for plugin with css assets
     *
     * @return void
     */
    public function testBeforeGetFilteredPropertiesForCssAssets(): void
    {
        $groupedCollectionMock = $this->getMockBuilder(GroupedCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $expected = [$this->assetInterfaceMock, []];
        $this->assetInterfaceMock->expects($this->once())->method('getContentType')->willReturn('css');
        $this->requestMock->expects($this->once())->method('getFullActionName')->willReturn('sales_order_create');
        $this->assertEquals($expected,
            $this->plugin->beforeGetFilteredProperties($groupedCollectionMock, $this->assetInterfaceMock
            )
        );
    }
}
