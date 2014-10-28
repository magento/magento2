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
namespace Magento\Sales\Block\Guest;

/**
 * Test class for \Magento\Sales\Block\Guest\Link
 */
class LinkTest extends \PHPUnit_Framework_TestCase
{
    public function testToHtml()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $context = $objectManagerHelper->getObject('Magento\Framework\View\Element\Template\Context');
        $httpContext = $this->getMockBuilder('\Magento\Framework\App\Http\Context')
            ->disableOriginalConstructor()
            ->setMethods(array('getValue'))
            ->getMock();
        $httpContext->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(true));

        /** @var \Magento\Sales\Block\Guest\Link $link */
        $link = $objectManagerHelper->getObject(
            'Magento\Sales\Block\Guest\Link',
            array(
                'context' => $context,
                'httpContext' => $httpContext,
            )
        );

        $this->assertEquals('', $link->toHtml());
    }
}
