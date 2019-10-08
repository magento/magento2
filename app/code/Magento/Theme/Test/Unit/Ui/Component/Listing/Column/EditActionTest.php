<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Ui\Component\Listing\Column\EditAction;

/**
 * Unit tests for  \Magento\Theme\Ui\Component\Listing\Column\EditAction.
 */
class EditActionTest extends \PHPUnit\Framework\TestCase
{
    /** @var EditAction */
    protected $component;

    /** @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $uiComponentFactory;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    public function setup()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->createMock(\Magento\Framework\View\Element\UiComponentFactory::class);
        $this->urlBuilder = $this->getMockForAbstractClass(
            \Magento\Framework\UrlInterface::class,
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
                    'label' => new \Magento\Framework\Phrase('Edit'),
                    '__disableTmpl' => true,
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
