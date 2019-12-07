<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Page;

use Magento\Framework;

/**
 * Class BuilderTest
 * @covers \Magento\Framework\View\Page\Builder
 */
class BuilderTest extends \Magento\Framework\View\Test\Unit\Layout\BuilderTest
{
    const CLASS_NAME = \Magento\Framework\View\Page\Builder::class;

    /**
     * @param array $arguments
     * @return \Magento\Framework\View\Page\Builder
     */
    protected function getBuilder($arguments)
    {
        $arguments['pageConfig'] = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $arguments['pageConfig']->expects($this->once())->method('setBuilder');
        $arguments['pageConfig']->expects($this->once())->method('getPageLayout')
            ->will($this->returnValue('test_layout'));

        $readerContext = $this->createMock(\Magento\Framework\View\Layout\Reader\Context::class);

        /** @var \PHPUnit_Framework_MockObject_MockObject $layout */
        $layout = & $arguments['layout'];
        $layout->expects($this->once())->method('getReaderContext')->will($this->returnValue($readerContext));

        $arguments['pageLayoutReader'] = $this->createMock(\Magento\Framework\View\Page\Layout\Reader::class);
        $arguments['pageLayoutReader']->expects($this->once())->method('read')->with($readerContext, 'test_layout');

        return parent::getBuilder($arguments);
    }

    /**
     * @return array
     */
    protected function getLayoutMockMethods()
    {
        $result = parent::getLayoutMockMethods();
        $result[] = 'getReaderContext';
        return $result;
    }
}
