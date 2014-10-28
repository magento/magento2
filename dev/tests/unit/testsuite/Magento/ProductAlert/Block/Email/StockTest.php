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
namespace Magento\ProductAlert\Block\Email;

/**
 * Test class for \Magento\ProductAlert\Block\Product\View\Stock
 */
class StockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\ProductAlert\Block\Product\View\Stock
     */
    protected $_block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filter\Input\MaliciousCode
     */
    protected $_filter;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_filter = $this->getMock(
            '\Magento\Framework\Filter\Input\MaliciousCode',
            array('filter'),
            array(),
            '',
            false
        );
        $this->_block = $objectManager->getObject(
            'Magento\ProductAlert\Block\Email\Stock',
            array('maliciousCode' => $this->_filter)
        );
    }

    /**
     * @dataProvider testGetFilteredContentDataProvider
     * @param $contentToFilter
     * @param $contentFiltered
     */
    public function testGetFilteredContent($contentToFilter, $contentFiltered)
    {
        $this->_filter->expects($this->once())->method('filter')->with($contentToFilter)
            ->will($this->returnValue($contentFiltered));
        $this->assertEquals($contentFiltered, $this->_block->getFilteredContent($contentToFilter));
    }

    public function testGetFilteredContentDataProvider()
    {
        return array(
            'normal desc' => array('<b>Howdy!</b>', '<b>Howdy!</b>'),
            'malicious desc 1' => array('<javascript>Howdy!</javascript>', 'Howdy!'),
        );
    }
}
