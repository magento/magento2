<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source\Website;

use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\System\Store;

/**
 * @api
 * @since 2.0.0
 */
class OptionHash implements ArrayInterface
{
    /**
     * System Store Model
     *
     * @var Store
     * @since 2.0.0
     */
    protected $_systemStore;

    /**
     * @var bool True if the default website (Admin) should be included
     * @since 2.0.0
     */
    protected $_withDefaultWebsite;

    /**
     * @param Store $systemStore
     * @param bool $withDefaultWebsite
     * @since 2.0.0
     */
    public function __construct(Store $systemStore, $withDefaultWebsite = false)
    {
        $this->_systemStore = $systemStore;
        $this->_withDefaultWebsite = $withDefaultWebsite;
    }

    /**
     * Return websites array
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return $this->_systemStore->getWebsiteOptionHash($this->_withDefaultWebsite);
    }
}
