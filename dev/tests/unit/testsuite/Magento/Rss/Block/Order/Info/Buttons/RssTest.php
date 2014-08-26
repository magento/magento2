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

namespace Magento\Rss\Block\Order\Info\Buttons;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class RssTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Rss\Block\Order\Info\Buttons\Rss */
    protected $rss;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\Registry */
    protected $registry;

    /** @var \Magento\Rss\Helper\Order|\PHPUnit_Framework_MockObject_MockObject */
    protected $rssOrderHelper;

    protected function setUp()
    {
        $this->context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->rssOrderHelper = $this->getMock('Magento\Rss\Helper\Order', [], [], '', false);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->registry = $this->objectManagerHelper->getObject('Magento\Framework\Registry');

        $this->rss = $this->objectManagerHelper->getObject(
            'Magento\Rss\Block\Order\Info\Buttons\Rss',
            [
                'context' => $this->context,
                'registry' => $this->registry,
                'orderHelper' => $this->rssOrderHelper
            ]
        );
    }

    public function testGetOrder()
    {
        $currentOrder = $this->getMock('Magento\Sales\Model\Order', [], [], '', false);
        $this->registry->register('current_order', $currentOrder);
        $this->assertEquals($currentOrder, $this->rss->getOrder());
    }

    public function testGetOrderHelper()
    {
        $orderHelper = $this->rss->getOrderHelper();
        $this->assertEquals($this->rssOrderHelper, $orderHelper);
    }
}
