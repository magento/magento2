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
namespace Magento\Sales\Block\Adminhtml\Order\Create;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Adminhtml order create gift message block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Giftmessage extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Gift message save
     *
     * @var \Magento\GiftMessage\Model\Save
     */
    protected $_giftMessageSave;

    /**
     * Message helper
     *
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $_messageHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\GiftMessage\Model\Save $giftMessageSave
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\GiftMessage\Model\Save $giftMessageSave,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        array $data = array()
    ) {
        $this->_messageHelper = $messageHelper;
        $this->_giftMessageSave = $giftMessageSave;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }

    /**
     * Generate form for editing of gift message for entity
     *
     * @param \Magento\Framework\Object $entity
     * @param string $entityType
     * @return string
     */
    public function getFormHtml(\Magento\Framework\Object $entity, $entityType = 'quote')
    {
        return $this->getLayout()->createBlock(
            'Magento\Sales\Block\Adminhtml\Order\Create\Giftmessage\Form'
        )->setEntity(
            $entity
        )->setEntityType(
            $entityType
        )->toHtml();
    }

    /**
     * Retrieve items allowed for gift messages.
     *
     * If no items available return false.
     *
     * @return array|false
     */
    public function getItems()
    {
        $items = array();
        $allItems = $this->getQuote()->getAllItems();

        foreach ($allItems as $item) {
            if ($this->_getGiftmessageSaveModel()->getIsAllowedQuoteItem(
                $item
            ) && $this->_messageHelper->getIsMessagesAvailable(
                'item',
                $item,
                $this->getStore()
            )
            ) {
                // if item allowed
                $items[] = $item;
            }
        }

        if (sizeof($items)) {
            return $items;
        }

        return false;
    }

    /**
     * Retrieve gift message save model
     *
     * @return \Magento\GiftMessage\Model\Save
     */
    protected function _getGiftmessageSaveModel()
    {
        return $this->_giftMessageSave;
    }
}
