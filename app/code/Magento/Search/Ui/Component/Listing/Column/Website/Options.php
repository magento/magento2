<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Ui\Component\Listing\Column\Website;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Class \Magento\Search\Ui\Component\Listing\Column\Website\Options
 *
 * @since 2.1.0
 */
class Options implements OptionSourceInterface
{

    /**
     * All Store Views value
     */
    const ALL_WEBSITES = '0';

    /**
     * Escaper
     *
     * @var Escaper
     * @since 2.1.0
     */
    protected $escaper;

    /**
     * System store
     *
     * @var SystemStore
     * @since 2.1.0
     */
    protected $systemStore;

    /**
     * Constructor
     *
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     * @since 2.1.0
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
     * @since 2.1.0
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

        $this->options = array_values($currentOptions);

        return $currentOptions;
    }
}
