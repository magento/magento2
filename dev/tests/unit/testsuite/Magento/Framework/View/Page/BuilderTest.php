<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\View\Page;

use Magento\Framework;

/**
 * Class BuilderTest
 * @covers \Magento\Framework\View\Page\Builder
 */
class BuilderTest extends \Magento\Framework\View\Layout\BuilderTest
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
        $layout =& $arguments['layout'];
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
