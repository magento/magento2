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
namespace Magento\Backend\Model;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\View
     */
    protected $_view;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $aclFilter = $this->getMock('Magento\Core\Model\Layout\Filter\Acl', array(), array(), '', false);
        $this->_layoutMock = $this->getMock('Magento\Core\Model\Layout', array(), array(), '', false);
        $layoutProcessor = $this->getMock('Magento\View\Layout\ProcessorInterface');
        $node = new \Magento\Simplexml\Element('<node/>');
        $this->_layoutMock->expects($this->once())->method('getNode')->will($this->returnValue($node));
        $this->_layoutMock->expects($this->any())->method('getUpdate')->will($this->returnValue($layoutProcessor));
        $this->_view = $helper->getObject(
            'Magento\Backend\Model\View',
            array(
                'aclFilter' => $aclFilter,
                'layout' => $this->_layoutMock,
                'request' => $this->getMock('Magento\App\Request\Http', array(), array(), '', false)
            )
        );
    }

    public function testLoadLayoutWhenBlockIsGenerate()
    {
        $this->_layoutMock->expects($this->once())->method('generateElements');
        $this->_view->loadLayout();
    }

    public function testLoadLayoutWhenBlockIsNotGenerate()
    {
        $this->_layoutMock->expects($this->never())->method('generateElements');
        $this->_view->loadLayout(null, false, true);
    }
}
