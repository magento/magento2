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
 * @category    Magento
 * @package     Magento_Page
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Page\Block;

class SwitchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Page\Block\Switcher
     */
    protected $_block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_coreHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appMock;

    protected function setUp()
    {
        $this->_storeManagerMock = $this->getMock('\Magento\Core\Model\StoreManagerInterface');
        $this->_coreHelperMock = $this->getMock('\Magento\Core\Helper\Data', array(), array(), '', false);
        $this->_contextMock = $this->getMock('\Magento\Core\Block\Template\Context', array(), array(), '', false);
        $this->_appMock = $this->getMock('\Magento\Core\Model\App', array(), array(), '', false);

        $this->_contextMock->expects($this->any())
            ->method('getApp')
            ->will($this->returnValue($this->_appMock));

        $this->_block = new \Magento\Page\Block\Switcher(
            $this->_storeManagerMock,
            $this->_coreHelperMock,
            $this->_contextMock
        );
    }

    /**
     * @dataProvider testIsStoreInUrlDataProvider
     */
    public function testIsStoreInUrl($isUseStoreInUrl)
    {
        $storeMock = $this->getMock('Magento\Core\Model\Store', array(), array(), '', false);
        $storeMock->expects($this->once())->method('isUseStoreInUrl')->will($this->returnValue($isUseStoreInUrl));

        $this->_storeManagerMock = $this->getMock('Magento\Core\Model\StoreManagerInterface');
        $this->_storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        $this->_appMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        $this->assertEquals($this->_block->isStoreInUrl(), $isUseStoreInUrl);
        // check value is cached
        $this->assertEquals($this->_block->isStoreInUrl(), $isUseStoreInUrl);
    }

    /**
     * @see self::testIsStoreInUrlDataProvider()
     * @return array
     */
    public function testIsStoreInUrlDataProvider()
    {
        return array(
            array(true),
            array(false),
        );
    }
}
