<?php
/**
 * \Magento\Webhook\Block\Adminhtml\Subscription\Edit
 *
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
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Block\Adminhtml\Subscription;

class EditTest extends \Magento\Test\Block\Adminhtml
{
    /** @var  \Magento\Core\Model\Registry */
    private $_registry;

    /** @var  \Magento\Webhook\Block\Adminhtml\Subscription\Edit */
    private $_block;

    /** @var  \Magento\Core\Helper\Data */
    protected $_coreData;

    protected function setUp()
    {
        parent::setUp();
        $this->_coreData = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
    }

    public function testGetHeaderTestExisting()
    {
        $subscriptionData = array(
            \Magento\Webhook\Block\Adminhtml\Subscription\Edit::DATA_SUBSCRIPTION_ID => true,
            'alias' => 'alias_value');

        $this->_registry = new \Magento\Core\Model\Registry();
        $this->_registry->register(
            \Magento\Webhook\Block\Adminhtml\Subscription\Edit::REGISTRY_KEY_CURRENT_SUBSCRIPTION,
            $subscriptionData);
        $this->_block = new \Magento\Webhook\Block\Adminhtml\Subscription\Edit(
            $this->_coreData,
            $this->_context,
            $this->_registry
        );
        $this->assertEquals('Edit Subscription', $this->_block->getHeaderText());

        $this->_registry->unregister(
            \Magento\Webhook\Block\Adminhtml\Subscription\Edit::REGISTRY_KEY_CURRENT_SUBSCRIPTION
        );
    }

    public function testGetHeaderTestNew()
    {
        $this->_registry = new \Magento\Core\Model\Registry();
        $this->_block = new \Magento\Webhook\Block\Adminhtml\Subscription\Edit(
            $this->_coreData,
            $this->_context,
            $this->_registry
        );

        $this->assertEquals('Add Subscription', $this->_block->getHeaderText());
    }
}
