<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source\Website;

use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\System\Store;

class OptionHash implements ArrayInterface
{
    /**
     * System Store Model
     *
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var bool True if the default website (Admin) should be included
     */
    protected $_withDefaultWebsite;

    /**
     * @param Store $systemStore
     * @param bool $withDefaultWebsite
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
     */
    public function toOptionArray()
    {
        return $this->_systemStore->getWebsiteOptionHash($this->_withDefaultWebsite);
    }
}
