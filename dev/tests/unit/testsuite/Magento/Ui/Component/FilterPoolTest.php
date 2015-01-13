<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Component;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface;
use Magento\Framework\View\Element\UiComponent\ConfigFactory;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Ui\Component\Filter\FilterPool as FilterPoolProvider;
use Magento\Ui\ContentType\ContentTypeFactory;

/**
 * Class ViewTest
 */
class FilterPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Filter var
     */
    const FILTER_VAR = 'filter';

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
     * @var FilterPoolProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterPoolProviderMock;

    /**
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->contextMock = $this->getMock(
            'Magento\Framework\View\Element\Template\Context',
            [],
            [],
            '',
            false
        );
        $this->renderContextMock = $this->getMock(
            'Magento\Framework\View\Element\UiComponent\Context',
            ['getNamespace', 'getStorage', 'getRequestParam'],
            [],
            '',
            false
        );
        $this->contentTypeFactoryMock = $this->getMock(
            'Magento\Ui\ContentType\ContentTypeFactory',
            [],
            [],
            '',
            false
        );
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
        $this->filterPoolProviderMock = $this->getMock(
            'Magento\Ui\Component\Filter\FilterPool',
            ['getFilter'],
            [],
            '',
            false
        );

        $this->filterPool = new FilterPool(
            $this->contextMock,
            $this->renderContextMock,
            $this->contentTypeFactoryMock,
            $this->configFactoryMock,
            $this->configBuilderMock,
            $this->dataProviderFactoryMock,
            $this->dataProviderManagerMock,
            $this->filterPoolProviderMock
        );
    }

    /**
     * Run test prepare method
     *
     * @return void
     */
    public function testPrepare()
    {
        /**
         * @var \Magento\Framework\View\Element\UiComponent\ConfigInterface
         * |\PHPUnit_Framework_MockObject_MockObject $configurationMock
         */
        $configurationMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ConfigInterface',
            ['getParentName'],
            '',
            false
        );
        /**
         * @var \Magento\Framework\View\Element\UiComponent\ConfigStorageInterface
         * |\PHPUnit_Framework_MockObject_MockObject $configStorageMock
         */
        $configStorageMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ConfigStorageInterface',
            ['addComponentsData', 'getDataCollection', 'getMeta'],
            '',
            false
        );
        /**
         * @var \Magento\Framework\Data\Collection|\PHPUnit_Framework_MockObject_MockObject $dataCollectionMock
         */
        $dataCollectionMock = $this->getMock(
            'Magento\Framework\Data\Collection',
            ['setOrder'],
            [],
            '',
            false
        );

        $this->renderContextMock->expects($this->at(0))
            ->method('getNamespace')
            ->will($this->returnValue('namespace'));
        $this->renderContextMock->expects($this->at(1))
            ->method('getNamespace')
            ->will($this->returnValue('namespace'));
        $this->configFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($configurationMock));
        $this->renderContextMock->expects($this->any())
            ->method('getStorage')
            ->will($this->returnValue($configStorageMock));
        $configStorageMock->expects($this->at(0))
            ->method('getDataCollection')
            ->will($this->returnValue($dataCollectionMock));
        $configStorageMock->expects($this->at(1))
            ->method('getDataCollection')
            ->will($this->returnValue($dataCollectionMock));

        $metaData = [
            'field-1' => 'value-1',
            'field-2' => 'value-2',
            'field-3' => 'value-3',
            'field-4' => 'value-4',
        ];
        $meta = [
            'fields' => $metaData,
        ];
        $filters = $metaData;

        $configStorageMock->expects($this->any())
            ->method('getMeta')
            ->will($this->returnValue($meta));
        $this->renderContextMock->expects($this->once())
            ->method('getRequestParam')
            ->with(static::FILTER_VAR);

        $filterMock = $this->getMockForAbstractClass(
            'Magento\Ui\Component\Filter\FilterInterface',
            ['getCondition'],
            '',
            false
        );

        $this->filterPoolProviderMock->expects($this->any())
            ->method('getFilter')
            ->will($this->returnValue($filterMock));
        $filterMock->expects($this->any())
            ->method('getCondition')
            ->will($this->returnValue(true));

        $dataCollectionMock->expects($this->any())
            ->method('addFieldToFilter');

        $this->assertNull($this->filterPool->prepare());
    }

    /**
     * Run test getFields method
     *
     * @return void
     */
    public function _testGetFields()
    {
        /** @var \Magento\Ui\Component\FilterPool|\PHPUnit_Framework_MockObject_MockObject $filterPool */
        $filterPool = $this->getMock(
            'Magento\Ui\Component\FilterPool',
            ['getParentName'],
            [
                $this->contextMock,
                $this->renderContextMock,
                $this->contentTypeFactoryMock,
                $this->configFactoryMock,
                $this->configBuilderMock,
                $this->dataProviderFactoryMock,
                $this->dataProviderManagerMock,
                $this->filterPoolProviderMock
            ],
            '',
            false
        );
        $filterPool->expects($this->any())
            ->method('getParentName')
            ->willReturn('parent');

        $result = [
            'field-1' => ['filterable' => 1],
            'field-4' => ['filterable' => 1],
        ];
        $meta = [
            'fields' => [
                'field-1' => ['filterable' => true],
                'field-2' => ['filterable' => false],
                'field-3' => ['filterable' => false],
                'field-4' => ['filterable' => true],
            ],
        ];
        /**
         * @var \Magento\Framework\View\Element\UiComponent\ConfigInterface
         * |\PHPUnit_Framework_MockObject_MockObject $configurationMock
         */
        $configMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ConfigInterface',
            [],
            '',
            false
        );
        /**
         * @var \Magento\Framework\View\Element\UiComponent\ConfigStorageInterface
         * |\PHPUnit_Framework_MockObject_MockObject $configStorageMock
         */
        $configStorageMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ConfigStorageInterface',
            ['addComponentsData', 'getDataCollection', 'getMeta'],
            '',
            false
        );

        $this->filterPool->setConfig($configMock);

        $this->renderContextMock->expects($this->any())
            ->method('getStorage')
            ->will($this->returnValue($configStorageMock));
        $configStorageMock->expects($this->any())
            ->method('getMeta')
            ->will($this->returnValue($meta));

        $this->assertEquals($result, $filterPool->getFields());
    }

    /**
     * Run test getActiveFilters method
     *
     * @return void
     */
    public function _testGetActiveFilters()
    {
        $result = [
            'field-1' => [
                'title' => 'title-1',
                'current_display_value' => 'value-1',
            ],
            'field-2' => [
                'title' => 'title-2',
                'current_display_value' => 'value-2',
            ],
            'field-3' => [
                'title' => 'title-3',
                'current_display_value' => 'value-3',
            ],
            'field-4' => [
                'title' => 'title-4',
                'current_display_value' => 'value-4',
            ],
        ];
        $meta = [
            'fields' => [
                'field-1' => [
                    'filter_type' => true,
                    'title' => 'title-1',
                ],
                'field-2' => [
                    'filter_type' => true,
                    'title' => 'title-2',
                ],
                'field-3' => [
                    'filter_type' => true,
                    'title' => 'title-3',
                ],
                'field-4' => [
                    'filter_type' => true,
                    'title' => 'title-4',
                ],
            ],
        ];
        $filters = [
            'field-1' => 'value-1',
            'field-2' => 'value-2',
            'field-3' => 'value-3',
            'field-4' => 'value-4',
        ];

        /**
         * @var \Magento\Framework\View\Element\UiComponent\ConfigInterface
         * |\PHPUnit_Framework_MockObject_MockObject $configurationMock
         */
        $configMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ConfigInterface',
            [],
            '',
            false
        );
        /**
         * @var \Magento\Framework\View\Element\UiComponent\ConfigStorageInterface
         * |\PHPUnit_Framework_MockObject_MockObject $configStorageMock
         */
        $configStorageMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ConfigStorageInterface',
            ['addComponentsData', 'getDataCollection', 'getMeta'],
            '',
            false
        );

        $this->filterPool->setConfig($configMock);

        $this->renderContextMock->expects($this->any())
            ->method('getStorage')
            ->will($this->returnValue($configStorageMock));
        $configStorageMock->expects($this->any())
            ->method('getMeta')
            ->will($this->returnValue($meta));
        $this->dataHelperMock->expects($this->once())
            ->method('prepareFilterString')
            ->will($this->returnValue($filters));
        $this->renderContextMock->expects($this->once())
            ->method('getRequestParam')
            ->with(static::FILTER_VAR);

        $this->assertEquals($result, $this->filterPool->getActiveFilters());
    }
}
