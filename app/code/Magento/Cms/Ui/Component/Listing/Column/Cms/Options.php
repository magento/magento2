<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Ui\Component\Listing\Column\Cms;

use Magento\Framework\Escaper;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Store\Ui\Component\Listing\Column\Store\Options as StoreOptions;

/**
 * Store Options for Cms Pages and Blocks
 */
class Options extends StoreOptions
{
    /**
     * All Store Views value
     */
    const ALL_STORE_VIEWS = '0';

    /**
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     */
    public function __construct(SystemStore $systemStore, Escaper $escaper)
    {
        parent::__construct($systemStore, $escaper);
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $websiteCollection = $this->systemStore->getWebsiteCollection();
        $groupCollection = $this->systemStore->getGroupCollection();
        $storeCollection = $this->systemStore->getStoreCollection();

        $this->currentOptions['All Store Views']['label'] = __('All Store Views');
        $this->currentOptions['All Store Views']['value'] = self::ALL_STORE_VIEWS;

        $this->generateCurrentOptions($websiteCollection, $groupCollection, $storeCollection);

        $this->options = array_values($this->currentOptions);

        return $this->options;
    }
}
