<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Plugin;

use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Csp\Plugin\AddDefaultPropertiesToGroupPlugin;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\App\State;
use Magento\Framework\App\Request\Http;
use Magento\Csp\Model\SubresourceIntegrity\SriEnabledActions;

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
     * @var MockObject
     */
    private MockObject $httpMock;

    /**
     * @var MockObject
     */
    private MockObject $sriEnabledActionsMock;

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
        $this->integrityRepositoryPoolMock = $this->createPartialMock(
            SubresourceIntegrityRepositoryPool::class,
            ['get']
        );
        $this->assetInterfaceMock = $this->createPartialMock(File::class, ['getPath']);
        $this->stateMock = $this->createPartialMock(State::class, ['getAreaCode']);
        $this->httpMock = $this->createPartialMock(Http::class, ['getFullActionName']);
        $this->sriEnabledActionsMock = $this->createPartialMock(
            SriEnabledActions::class,
            ['isPaymentPageAction']
        );
        $this->plugin = new AddDefaultPropertiesToGroupPlugin(
            $this->stateMock,
            $this->integrityRepositoryPoolMock,
            $this->httpMock,
            $this->sriEnabledActionsMock
        );
    }

    /**
     * Test for plugin with Js assets
     *
     * @return void
     * @throws LocalizedException
     */
    public function testBeforeGetFilteredProperties(): void
    {
        $actionName = "sales_order_create_index";
        $this->sriEnabledActionsMock->expects($this->once())->method('isPaymentPageAction')->willReturn(true);
        $this->httpMock->expects($this->once())->method('getFullActionName')->willReturn($actionName);
        $integrityRepositoryMock = $this->createPartialMock(
            SubresourceIntegrityRepository::class,
            ['getByPath']
        );
        $groupedCollectionMock = $this->createMock(GroupedCollection::class);
        $path = 'jquery.js';
        $area = 'base';

        $data = new SubresourceIntegrity(
            [
                'hash' => 'testhash',
                'path' => $path
            ]
        );
        $properties['attributes']['integrity'] = $data->getHash();
        $properties['attributes']['crossorigin'] = 'anonymous';
        $expected = [$this->assetInterfaceMock, $properties];
        $this->integrityRepositoryPoolMock->expects($this->once())->method('get')->with($area)
            ->willReturn(
                $integrityRepositoryMock
            );
        $this->assetInterfaceMock->expects($this->once())->method('getPath')->willReturn($path);
        $integrityRepositoryMock->expects($this->once())->method('getByPath')->with($path)->willReturn($data);
        $this->assertEquals(
            $expected,
            $this->plugin->beforeGetFilteredProperties(
                $groupedCollectionMock,
                $this->assetInterfaceMock
            )
        );
    }
}
