<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface;
use Magento\Framework\View\Element\UiComponent\ConfigFactory;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Ui\ContentType\ContentTypeFactory;

class PagingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Paging
     */
    protected $view;

    /**
     * @var ConfigBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurationBuilderMock;

    /**
     * @var TemplateContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $renderContextMock;

    /**
     * @var ContentTypeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentTypeFactoryMock;

    /**
     * @var ConfigFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configFactoryMock;

    /**
     * @var ConfigBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configBuilderMock;

    /**
     * @var \Magento\Ui\DataProvider\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataProviderFactoryMock;

    /**
     * @var \Magento\Ui\DataProvider\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataProviderManagerMock;

    /**
     * @var Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetRepoMock;

    public function setUp()
    {
        $this->renderContextMock = $this->getMock(
            'Magento\Framework\View\Element\UiComponent\Context',
            ['getNamespace', 'getStorage', 'getRequestParam'],
            [],
            '',
            false
        );
        $this->contextMock = $this->getMock(
            'Magento\Framework\View\Element\Template\Context',
            ['getAssetRepository'],
            [],
            '',
            false
        );
        $this->contentTypeFactoryMock = $this->getMock('Magento\Ui\ContentType\ContentTypeFactory', [], [], '', false);
        $this->configurationBuilderMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface'
        );
        $this->assetRepoMock = $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->contextMock->expects($this->any())->method('getAssetRepository')->willReturn($this->assetRepoMock);

        $this->configFactoryMock = $this->getMock(
            'Magento\Framework\View\Element\UiComponent\ConfigFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->configBuilderMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface',
            [],
            '',
            false
        );
        $this->dataProviderFactoryMock = $this->getMock(
            'Magento\Ui\DataProvider\Factory',
            [],
            [],
            '',
            false
        );
        $this->dataProviderManagerMock = $this->getMock(
            'Magento\Ui\DataProvider\Manager',
            [],
            [],
            '',
            false
        );

        $this->view = new \Magento\Ui\Component\Paging(
            $this->contextMock,
            $this->renderContextMock,
            $this->contentTypeFactoryMock,
            $this->configFactoryMock,
            $this->configBuilderMock,
            $this->dataProviderFactoryMock,
            $this->dataProviderManagerMock
        );
    }

    public function testPrepare()
    {
        $paramsSize = 20;
        $paramsPage = 1;
        $nameSpace = 'namespace';
        $configurationMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ConfigInterface',
            ['getData'],
            '',
            false
        );
        $this->renderContextMock->expects($this->any())->method('getNamespace')->willReturn($nameSpace);
        $this->configFactoryMock->expects($this->once())->method('create')->willReturn($configurationMock);

        $storageMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ConfigStorageInterface'
        );
        $dataCollectionMock = $this->getMockForAbstractClass(
            '\Magento\Framework\Data\CollectionDataSourceInterface',
            [],
            '',
            false,
            true,
            true,
            ['setLimit']
        );

        $this->renderContextMock->expects($this->any())->method('getStorage')->willReturn($storageMock);

        $storageMock->expects($this->once())->method('getDataCollection')->willReturn($dataCollectionMock);

        $configurationMock->expects($this->at(0))->method('getData')->with('current')->willReturn($paramsPage);

        $configurationMock->expects($this->at(1))->method('getData')->with('pageSize')->willReturn($paramsSize);
        $this->renderContextMock->expects($this->atLeastOnce())
            ->method('getRequestParam')
            ->willReturnMap(
                [
                    ['page', $paramsPage, $paramsPage],
                    ['limit', $paramsSize, $paramsSize],
                ]
            );

        $dataCollectionMock->expects($this->any())
            ->method('setLimit')
            ->with($paramsPage, $paramsSize)
            ->willReturnSelf();

        $this->assertNull($this->view->prepare());
    }
}
