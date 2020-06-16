<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Ui\Component\Listing\Column\EditAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class EditAction test for Listing Column
 */
class EditActionTest extends TestCase
{
    /** @var EditAction */
    protected $component;

    /** @var ContextInterface|MockObject */
    protected $context;

    /** @var UiComponentFactory|MockObject */
    protected $uiComponentFactory;

    /** @var UrlInterface|MockObject */
    protected $urlBuilder;

    protected function setup(): void
    {
        $this->context = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->createMock(UiComponentFactory::class);
        $this->urlBuilder = $this->getMockForAbstractClass(
            UrlInterface::class,
            [],
            '',
            false
        );
        $this->component = new EditAction(
            $this->context,
            $this->uiComponentFactory,
            $this->urlBuilder,
            [],
            [
                'name' => 'name',
                'config' => ['editUrlPath' => 'theme/design_config/edit']
            ]
        );
    }

    /**
     * @param array $dataSourceItem
     * @param string $scope
     * @param int $scopeId
     *
     * @dataProvider getPrepareDataSourceDataProvider
     */
    public function testPrepareDataSource($dataSourceItem, $scope, $scopeId)
    {
        $expectedDataSourceItem = [
            'name' => [
                'edit' => [
                    'href' => 'http://magento.com/theme/design_config/edit',
                    'label' => new Phrase('Edit'),
                ]
            ],
        ];

        $expectedDataSource = ['data' => ['items' => [array_merge($expectedDataSourceItem, $dataSourceItem)]]];
        $this->urlBuilder->expects($this->any())
            ->method('getUrl')
            ->with(
                'theme/design_config/edit',
                ['scope' => $scope, 'scope_id' => $scopeId]
            )
            ->willReturn('http://magento.com/theme/design_config/edit');
        $dataSource = ['data' => ['items' => [$dataSourceItem]]];
        $dataSource = $this->component->prepareDataSource($dataSource);

        $this->assertEquals($expectedDataSource, $dataSource);
    }

    /**
     * @return array
     */
    public function getPrepareDataSourceDataProvider()
    {
        return [
            [['entity_id' => 1], ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null],
            [['entity_id' => 1, 'store_id' => 2, 'store_website_id' => 1], ScopeInterface::SCOPE_STORES, 2],
            [['entity_id' => 1, 'store_website_id' => 1], ScopeInterface::SCOPE_WEBSITES, 1],
        ];
    }
}
