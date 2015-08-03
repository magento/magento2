<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\Address;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Object;

/**
 * Class Renderer used for formatting a store address
 */
class Renderer
{
    /**
     * Template used to format store address information
     */
    const DEFAULT_STORE_ADDRESS_FORMAT = "{{var name}}\n"
        . "{{var street_line1}}\n"
        . "{{depend street_line2}}{{var street_line2}}\n{{/depend}}"
        . "{{var city}}, {{var region}} {{var postcode}},\n"
        . "{{var country}}";

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * Constructor
     *
     * @param EventManager $eventManager
     * @param FilterManager $filterManager
     */
    public function __construct(EventManager $eventManager, FilterManager $filterManager)
    {
        $this->eventManager = $eventManager;
        $this->filterManager = $filterManager;
    }

    /**
     * Format address in a specific way
     *
     * @param Object $storeInfo
     * @param $type
     * @return string
     */
    public function format(Object $storeInfo, $type = 'html')
    {
        $this->eventManager->dispatch('store_address_format', ['type' => $type, 'store_info' => $storeInfo]);
        $address = $this->filterManager->template(
            self::DEFAULT_STORE_ADDRESS_FORMAT,
            ['variables' => $storeInfo->getData()]
        );

        if ($type == 'html') {
            $address = nl2br($address);
        }
        return $address;
    }
}
