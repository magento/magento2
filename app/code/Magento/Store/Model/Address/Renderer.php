<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    const DEFAULT_TEMPLATE = "{{var name}}\n" .
        "{{var street_line1}}\n" .
        "{{depend street_line2}}{{var street_line2}}\n{{/depend}}" .
        "{{depend city}}{{var city}},{{/depend}} {{var region}} {{depend postcode}}{{var postcode}},{{/depend}}\n" .
        "{{var country}}";

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var string
     */
    private $template;

    /**
     * Constructor
     *
     * @param EventManager $eventManager
     * @param FilterManager $filterManager
     * @param string $template
     */
    public function __construct(
        EventManager $eventManager,
        FilterManager $filterManager,
        $template = self::DEFAULT_TEMPLATE
    ) {
        $this->eventManager = $eventManager;
        $this->filterManager = $filterManager;
        $this->template = $template;
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
            $this->template,
            ['variables' => $storeInfo->getData()]
        );

        if ($type == 'html') {
            $address = nl2br($address);
        }
        return $address;
    }
}
