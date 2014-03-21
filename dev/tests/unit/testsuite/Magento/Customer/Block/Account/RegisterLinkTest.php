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
 * @package     Magento_Customer
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Block\Account;

/**
 * Test class for \Magento\Customer\Block\Account\RegisterLink
 */
class RegisterLinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testToHtml()
    {
        $context = $this->_objectManager->getObject('Magento\View\Element\Template\Context');
        $httpContext = $this->getMockBuilder('Magento\App\Http\Context')
            ->disableOriginalConstructor()
            ->setMethods(array('getValue'))
            ->getMock();
        $httpContext->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(true));

        /** @var \Magento\Sales\Block\Guest\Link $link */
        $link = $this->_objectManager->getObject(
            'Magento\Customer\Block\Account\RegisterLink',
            array(
                'context' => $context,
                'httpContext' => $httpContext,
            )
        );

        $this->assertEquals('', $link->toHtml());
    }

    public function testGetHref()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $helper = $this->getMockBuilder(
            'Magento\Customer\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            array('getRegisterUrl')
        )->getMock();

        $helper->expects($this->any())->method('getRegisterUrl')->will($this->returnValue('register url'));

        $context = $this->_objectManager->getObject('Magento\View\Element\Template\Context');

        $block = $this->_objectManager->getObject(
            'Magento\Customer\Block\Account\RegisterLink',
            array('context' => $context, 'customerHelper' => $helper)
        );
        $this->assertEquals('register url', $block->getHref());
    }
}
