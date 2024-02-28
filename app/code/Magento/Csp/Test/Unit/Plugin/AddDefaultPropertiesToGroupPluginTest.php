<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Plugin;

use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Csp\Plugin\AddDefaultPropertiesToGroupPlugin;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\App\State;

/**
 * Test for class Magento\Csp\Plugin\AddDefaultPropertiesToGroupPlugin
 *
 */
class AddDefaultPropertiesToGroupPluginTest extends TestCase
{

    /**
     * @var MockObject
     */
    private MockObject $assetInterfaceMock;

    /**
     * @var MockObject
     */
    private MockObject $integrityRepositoryPoolMock;

    /**
     * @var MockObject
     */
    private MockObject $stateMock;

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
        $this->integrityRepositoryPoolMock = $this->getMockBuilder(SubresourceIntegrityRepositoryPool::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $this->assetInterfaceMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrl', 'getContentType'])
            ->getMockForAbstractClass();
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAreaCode'])
            ->getMock();
        $this->plugin = new AddDefaultPropertiesToGroupPlugin(
            $this->stateMock,
            $this->integrityRepositoryPoolMock
        );
    }

    /**
     * Test for plugin with Js assets
     *
     * @return void
     */
    public function testBeforeGetFilteredProperties(): void
    {
        $integrityRepositoryMock = $this->getMockBuilder(SubresourceIntegrityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getByUrl'])
            ->getMock();
        $groupedCollectionMock = $this->getMockBuilder(GroupedCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $url = 'https://magento.test/static/version1708401324/frontend/Magento/luma/en_US/jquery.js';
        $area = 'frontend';

        $data = new SubresourceIntegrity(
            [
                'hash' => 'testhash',
                'url' => $url
            ]
        );
        $properties['attributes']['integrity'] = $data->getHash();
        $properties['attributes']['crossorigin'] = 'anonymous';
        $expected = [$this->assetInterfaceMock, $properties];
        $this->stateMock->expects($this->once())->method('getAreaCode')->willReturn($area);
        $this->integrityRepositoryPoolMock->expects($this->once())->method('get')->with($area)->willReturn($integrityRepositoryMock);
        $this->assetInterfaceMock->expects($this->once())->method('getUrl')->willReturn($url);
        $integrityRepositoryMock->expects($this->once())->method('getByUrl')->with($url)->willReturn($data);
        $this->assertEquals($expected,
            $this->plugin->beforeGetFilteredProperties($groupedCollectionMock, $this->assetInterfaceMock
            )
        );
    }
}
