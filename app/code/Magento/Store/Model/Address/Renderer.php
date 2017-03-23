<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\Address;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\DataObject;

/**
 * Class Renderer used for formatting a store address
 */
class Renderer
{
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
    public function __construct(
        EventManager $eventManager,
        FilterManager $filterManager
    ) {
        $this->eventManager = $eventManager;
        $this->filterManager = $filterManager;
    }

    /**
     * Format address in a specific way
     *
     * @param DataObject $storeInfo
     * @param string $type
     * @return string
     */
    public function format(DataObject $storeInfo, $type = 'html')
    {
        $this->eventManager->dispatch('store_address_format', ['type' => $type, 'store_info' => $storeInfo]);
        $address = $this->filterManager->template(
            "{{var name}}\n{{var street_line1}}\n{{depend street_line2}}{{var street_line2}}\n{{/depend}}"
            . "{{var city}}, {{var region}} {{var postcode}},\n{{var country}}",
            ['variables' => $storeInfo->getData()]
        );

        if ($type == 'html') {
            $address = nl2br($address);
        }
        return $address;
    }
}
