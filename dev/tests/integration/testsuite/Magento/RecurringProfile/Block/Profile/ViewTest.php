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
 * @package     Magento_Sales
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\RecurringProfile\Block\Profile;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\RecurringProfile\Block\Profile\View
     */
    protected $_block;

    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\RecurringProfile\Model\Profile
     */
    protected $_profile;

    protected function setUp()
    {
        $this->_profile = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\RecurringProfile\Model\Profile');
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Registry')->register('current_recurring_profile', $this->_profile);

        $this->_layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\View\LayoutInterface');
        $this->_block = $this->_layout->createBlock('Magento\RecurringProfile\Block\Profile\View', 'block');
    }

    protected function tearDown()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Registry')->unregister('current_recurring_profile');
        $this->_profile = null;
        $this->_block = null;
        $this->_layout = null;
    }

    public function testToHtmlPropagatesUrl()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\State')
            ->setAreaCode('frontend');
        $this->_block->setShouldPrepareInfoTabs(true);
        $childOne = $this->_layout->addBlock('Magento\View\Element\Text', 'child1', 'block');
        $this->_layout->addToParentGroup('child1', 'info_tabs');
        $childTwo = $this->_layout->addBlock('Magento\View\Element\Text', 'child2', 'block');
        $this->_layout->addToParentGroup('child2', 'info_tabs');

        $this->assertEmpty($childOne->getViewUrl());
        $this->assertEmpty($childTwo->getViewUrl());
        $this->_block->toHtml();
        $this->assertNotEmpty($childOne->getViewUrl());
        $this->assertNotEmpty($childTwo->getViewUrl());
    }
}
