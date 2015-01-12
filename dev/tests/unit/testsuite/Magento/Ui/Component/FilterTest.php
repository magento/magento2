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
 * Class FilterTest
 */
class FilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TemplateContext||\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataHelperMock;

    /**
     * @var FilterPoolProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterPoolMock;

    /**
     * @var \Magento\Ui\Component\FilterPool
     */
    protected $filter;

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
        $this->filterPoolMock = $this->getMock(
            'Magento\Ui\Component\Filter\FilterPool',
            ['getFilter'],
            [],
            '',
            false
        );

        $this->filter = new \Magento\Ui\Component\FilterPool(
            $this->contextMock,
            $this->renderContextMock,
            $this->contentTypeFactoryMock,
            $this->configFactoryMock,
            $this->configBuilderMock,
            $this->dataProviderFactoryMock,
            $this->dataProviderManagerMock,
            $this->filterPoolMock
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
        $configStorageMock->expects($this->once())
            ->method('addComponentsData')
            ->with($configurationMock);

        $this->assertNull($this->filter->prepare());
    }
}
