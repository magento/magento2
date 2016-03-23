<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Ui\Component\Listing\Column\Scope;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $escaper;

    /**
     * System store
     *
     * @var SystemStore
     */
    protected $systemStore;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $currentOptions = [];

    /**
     * Constructor
     *
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     */
    public function __construct(SystemStore $systemStore, Escaper $escaper)
    {
        $this->systemStore = $systemStore;
        $this->escaper = $escaper;
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
        $this->generateCurrentOptions();
        $this->options = array_values($this->currentOptions);
        return $this->options;
    }

    /**
     * Generate current options
     *
     * @return void
     */
    protected function generateCurrentOptions()
    {
        $websiteCollection = $this->systemStore->getWebsiteCollection();
        $groupCollection = $this->systemStore->getGroupCollection();
        $storeCollection = $this->systemStore->getStoreCollection();

        // Add option to select All Websites
        $this->currentOptions['All Websites']['label'] = $this->escaper->escapeHtml(__('All Websites'));
        $this->currentOptions['All Websites']['value'] =
            \Magento\Search\Ui\Component\Listing\Column\Website\Options::ALL_WEBSITES
            . ':'
            . \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        foreach ($websiteCollection as $website) {
            $groups = [];
            /** @var \Magento\Store\Model\Group $group */
            foreach ($groupCollection as $group) {
                if ($group->getWebsiteId() == $website->getId()) {
                    $stores = [];
                    /** @var  \Magento\Store\Model\Store $store */

                    // Add an option for All store views for this website
                    $stores['All Store Views']['label'] = $this->escaper->escapeHtml(__('    All Store Views'));
                    $stores['All Store Views']['value'] =
                        $website->getId()
                        . ':'
                        . \Magento\Store\Model\Store::DEFAULT_STORE_ID;

                    /** @var \Magento\Store\Model\Website $website */
                    foreach ($storeCollection as $store) {
                        if ($store->getGroupId() == $group->getId()) {
                            $name = $this->escaper->escapeHtml($store->getName());
                            $stores[$name]['label'] = str_repeat(' ', 8) . $name;
                            $stores[$name]['value'] = $website->getId() . ':' . $store->getId();
                        }
                    }

                    // Add parent Store group
                    $name = $this->escaper->escapeHtml($group->getName());
                    $groups[$name]['label'] = str_repeat(' ', 4) . $name;
                    $groups[$name]['value'] = array_values($stores);
                }
            }
            $name = $this->escaper->escapeHtml($website->getName());
            $this->currentOptions[$name]['label'] = $name;
            $this->currentOptions[$name]['value'] = array_values($groups);
        }

    }
}
