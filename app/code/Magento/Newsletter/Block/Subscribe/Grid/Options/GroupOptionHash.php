<?php
/**
 * Newsletter group options
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Block\Subscribe\Grid\Options;

/**
 * Class \Magento\Newsletter\Block\Subscribe\Grid\Options\GroupOptionHash
 *
 * @since 2.0.0
 */
class GroupOptionHash implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * System Store Model
     *
     * @var \Magento\Store\Model\System\Store
     * @since 2.0.0
     */
    protected $_systemStore;

    /**
     * @param \Magento\Store\Model\System\Store $systemStore
     * @since 2.0.0
     */
    public function __construct(\Magento\Store\Model\System\Store $systemStore)
    {
        $this->_systemStore = $systemStore;
    }

    /**
     * Return store group array
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return $this->_systemStore->getStoreGroupOptionHash();
    }
}
