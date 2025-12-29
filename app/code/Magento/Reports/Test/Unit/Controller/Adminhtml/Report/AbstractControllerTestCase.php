<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Menu;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractControllerTestCase extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var FileFactory|MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var ViewInterface|MockObject
     */
    protected $viewMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var BlockInterface|MockObject
     */
    protected $breadcrumbsBlockMock;

    /**
     * @var BlockInterface|MockObject
     */
    protected $menuBlockMock;

    /**
     * @var BlockInterface|MockObject
     */
    protected $switcherBlockMock;

    /**
     * @var Menu|MockObject
     */
    protected $menuModelMock;

    /**
     * @var AbstractBlock|MockObject
     */
    protected $abstractBlockMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->createPartialMock(
            HttpRequest::class,
            ['isDispatched', 'initForward', 'setDispatched', 'isForwarded']
        );
        $this->breadcrumbsBlockMock = $this->createPartialMockWithReflection(
            BlockInterface::class,
            ['addLink', 'toHtml']
        );
        $this->menuBlockMock = $this->createPartialMockWithReflection(
            BlockInterface::class,
            ['setActive', 'getMenuModel', 'toHtml']
        );
        $this->viewMock = $this->createMock(ViewInterface::class);

        $this->layoutMock = $this->createMock(LayoutInterface::class);
        $this->switcherBlockMock = $this->createMock(BlockInterface::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->fileFactoryMock = $this->createMock(FileFactory::class);
        $this->menuModelMock = $this->createMock(Menu::class);
        $this->abstractBlockMock = $this->createPartialMockWithReflection(
            AbstractBlock::class,
            ['getCsvFile', 'getExcelFile', 'setSaveParametersInSession', 'getCsv', 'getExcel']
        );

        $this->menuModelMock->expects($this->any())->method('getParentItems')->willReturn([]);
        $this->menuBlockMock->expects($this->any())->method('getMenuModel')->willReturn($this->menuModelMock);
        $this->viewMock->expects($this->any())->method('getLayout')->willReturn($this->layoutMock);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);

        $this->layoutMock->expects($this->any())->method('getBlock')->willReturnMap(
            [
                ['breadcrumbs', $this->breadcrumbsBlockMock],
                ['menu', $this->menuBlockMock],
                ['store_switcher', $this->switcherBlockMock]
            ]
        );
        $this->layoutMock->expects($this->any())->method('getChildBlock')->willReturn($this->abstractBlockMock);
    }

    /**
     * Custom mock for abstract class
     * @param string $className
     * @param array $mockedMethods
     * @return MockObject
     */
    protected function getMockForAbstractClassBuilder($className, $mockedMethods = [])
    {
        if (empty($mockedMethods)) {
            return $this->createMock($className);
        }
        return $this->createPartialMock($className, $mockedMethods);
    }
}
