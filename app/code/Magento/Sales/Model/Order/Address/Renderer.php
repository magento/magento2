<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Address;

use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\Order\Address;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\App\ObjectManager;

/**
 * Class Renderer used for formatting an order address
 * @api
 * @since 100.0.2
 */
class Renderer
{
    /**
     * @var AddressConfig
     */
    protected $addressConfig;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var StringUtils
     */
    private $stringUtils;

    /**
     * Constructor
     *
     * @param AddressConfig $addressConfig
     * @param EventManager $eventManager
     * @param StringUtils $stringUtils
     */
    public function __construct(
        AddressConfig $addressConfig,
        EventManager $eventManager,
        StringUtils $stringUtils = null
    ) {
        $this->addressConfig = $addressConfig;
        $this->eventManager = $eventManager;
        $this->stringUtils = $stringUtils ?: ObjectManager::getInstance()->get(StringUtils::class);
    }

    /**
     * Format address in a specific way
     *
     * @param Address $address
     * @param string $type
     * @return string|null
     */
    public function format(Address $address, $type)
    {
        $this->addressConfig->setStore($address->getOrder()->getStoreId());
        $formatType = $this->addressConfig->getFormatByCode($type);
        if (!$formatType || !$formatType->getRenderer()) {
            return null;
        }
        $this->eventManager->dispatch('customer_address_format', ['type' => $formatType, 'address' => $address]);
        return $formatType->getRenderer()->renderArray($address->getData());
    }

    /**
     * Detect an input string is Arabic
     *
     * @param string $subject
     * @return bool
     */
    public function isArabic(string $subject): bool
    {
        return (preg_match('/\p{Arabic}/u', $subject) > 0);
    }

    /**
     * Reverse text with Arabic characters
     *
     * @param string $string
     * @return string
     */
    public function reverseArabicText($string)
    {
        $splitText = explode(' ', $string);
        for ($i = 0; $i < count($splitText); $i++) {
            if ($this->isArabic($splitText[$i])) {
                for ($j = $i + 1; $j < count($splitText); $j++) {
                    $tmp = ($this->isArabic($splitText[$j]))
                        ? $this->stringUtils->strrev($splitText[$j]) : $splitText[$j];
                    $splitText[$j] = ($this->isArabic($splitText[$i]))
                        ? $this->stringUtils->strrev($splitText[$i]) : $splitText[$i];
                    $splitText[$i] = $tmp;
                }
            }
        }
        return implode(' ', $splitText);
    }

    /**
     * Check and revert arabic text
     *
     * @param string $string
     * @return string
     */
    public function processArabicText($string)
    {
        return ($this->isArabic($string))
            ? $this->reverseArabicText($string) : $string;
    }
}
