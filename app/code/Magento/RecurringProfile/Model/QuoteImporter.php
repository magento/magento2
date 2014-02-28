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

/**
 * Recurring profile quote model
 */
namespace Magento\RecurringProfile\Model;

class QuoteImporter
{
    /**
     * @var \Magento\RecurringProfile\Model\ProfileFactory
     */
    protected $_profileFactory;

    /**
     * @param \Magento\RecurringProfile\Model\ProfileFactory $profileFactory
     */
    public function __construct(\Magento\RecurringProfile\Model\ProfileFactory $profileFactory)
    {
        $this->_profileFactory = $profileFactory;
    }

    /**
     * Prepare recurring payment profiles
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @throws \Exception
     * @return array
     */
    public function prepareRecurringPaymentProfiles(\Magento\Sales\Model\Quote $quote)
    {
        if (!$quote->getTotalsCollectedFlag()) {
            throw new \Exception('Quote totals must be collected before this operation.');
        }

        $result = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if (is_object($product) && ($product->isRecurring())
                && $profile = $this->_profileFactory->create()->importProduct($product)
            ) {
                $profile->importQuote($quote);
                $profile->importQuoteItem($item);
                $result[] = $profile;
            }
        }
        return $result;
    }
}
