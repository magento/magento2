<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Ui\Component\Listing\Column\Scope;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;
use Magento\Search\Ui\Component\Listing\Column\Website\Options as ColumnWebsiteOptions;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Store\Model\Website;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
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
     * @param SystemStore $systemStore System store
     * @param Escaper $escaper Escaper
     */
    public function __construct(
        protected readonly SystemStore $systemStore,
        protected readonly Escaper $escaper
    ) {
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
            ColumnWebsiteOptions::ALL_WEBSITES
            . ':'
            . Store::DEFAULT_STORE_ID;

        foreach ($websiteCollection as $website) {
            $groups = [];
            /** @var Group $group */
            foreach ($groupCollection as $group) {
                if ($group->getWebsiteId() == $website->getId()) {
                    $stores = [];
                    /** @var Store $store */

                    // Add an option for All store views for this website
                    $stores['All Store Views']['label'] = $this->escaper->escapeHtml(__('    All Store Views'));
                    $stores['All Store Views']['value'] =
                        $website->getId()
                        . ':'
                        . Store::DEFAULT_STORE_ID;

                    /** @var Website $website */
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
