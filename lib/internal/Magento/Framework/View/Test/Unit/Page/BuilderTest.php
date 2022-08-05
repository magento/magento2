<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Page;

use Magento\Framework\View\Layout\Reader\Context;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;
use Magento\Framework\View\Page\Builder;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Layout\Reader;
use Magento\Framework\View\PageLayout\Config as PageLayoutConfig;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Magento\Framework\View\Page\Builder
 */
class BuilderTest extends \Magento\Framework\View\Test\Unit\Layout\BuilderTest
{
    const CLASS_NAME = Builder::class;

    /**
     * @param array $arguments
     * @return object
     */
    protected function getBuilder($arguments)
    {
        $arguments['pageConfig'] = $this->createMock(Config::class);
        $arguments['pageConfig']->expects($this->once())->method('setBuilder');
        $arguments['pageConfig']->expects($this->once())->method('getPageLayout')
            ->willReturn('test_layout');

        $readerContext = $this->createMock(Context::class);

        /** @var MockObject $layout */
        $layout = & $arguments['layout'];
        $layout->expects($this->once())->method('getReaderContext')->willReturn($readerContext);

        $arguments['pageLayoutReader'] = $this->createMock(Reader::class);
        $arguments['pageLayoutReader']->expects($this->once())->method('read')->with($readerContext, 'test_layout');
        $pageLayoutConfig = $this->createMock(PageLayoutConfig::class);
        $arguments['pageLayoutBuilder'] = $this->getMockForAbstractClass(BuilderInterface::class);
        $arguments['pageLayoutBuilder']->expects($this->once())
            ->method('getPageLayoutsConfig')
            ->willReturn($pageLayoutConfig);
        $pageLayoutConfig->expects($this->once())
            ->method('hasPageLayout')
            ->with('test_layout')
            ->willReturn(true);

        return parent::getBuilder($arguments);
    }

    /**
     * @return array
     */
    protected function getLayoutMockMethods(): array
    {
        $result = parent::getLayoutMockMethods();
        $result[] = 'getReaderContext';

        return $result;
    }
}
