<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote\Address;

/**
 * Quote addresses collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_quote_address_collection';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'quote_address_collection';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Quote\Model\Quote\Address', 'Magento\Quote\Model\ResourceModel\Quote\Address');
    }

    /**
     * Setting filter on quote_id field but if quote_id is 0
     * we should exclude loading junk data from DB
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteFilter($quoteId)
    {
        $this->addFieldToFilter('quote_id', $quoteId ? $quoteId : ['null' => 1]);
        return $this;
    }

    /**
     * Redeclare after load method for dispatch event
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        $this->_eventManager->dispatch($this->_eventPrefix . '_load_after', [$this->_eventObject => $this]);

        return $this;
    }
}
