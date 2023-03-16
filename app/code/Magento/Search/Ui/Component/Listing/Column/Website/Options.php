<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Ui\Component\Listing\Column\Website;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store as SystemStore;

class Options implements OptionSourceInterface
{

    /**
     * All Store Views value
     */
    const ALL_WEBSITES = '0';

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
        $currentOptions['']['label'] = '--';
        $currentOptions['']['value'] = '--';

        $currentOptions['All Store Views']['label'] = __('All Websites');
        $currentOptions['All Store Views']['value'] = self::ALL_WEBSITES;

        $websiteCollection = $this->systemStore->getWebsiteCollection();

        foreach ($websiteCollection as $website) {
            $name = $this->escaper->escapeHtml($website->getName());
            $currentOptions[$name]['label'] = $name;
            $currentOptions[$name]['value'] = $website->getId();
        }

        return $currentOptions;
    }
}
