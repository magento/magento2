<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
    const CLASS_NAME = 'Magento\Framework\View\Page\Builder';

    /**
     * @param array $arguments
     * @return \Magento\Framework\View\Page\Builder
     */
    protected function getBuilder($arguments)
    {
        $arguments['pageConfig'] = $this->getMock('Magento\Framework\View\Page\Config', [], [], '', false);
        $arguments['pageConfig']->expects($this->once())->method('setBuilder');
        $arguments['pageConfig']->expects($this->once())->method('getPageLayout')
            ->will($this->returnValue('test_layout'));

        $readerContext = $this->getMock('Magento\Framework\View\Layout\Reader\Context', [], [], '', false);

        /** @var \PHPUnit_Framework_MockObject_MockObject $layout */
        $layout = & $arguments['layout'];
        $layout->expects($this->once())->method('getReaderContext')->will($this->returnValue($readerContext));

        $arguments['pageLayoutReader'] = $this->getMock('Magento\Framework\View\Page\Layout\Reader', [], [], '', false);
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
