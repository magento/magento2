<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\JsonValidator;

/**
 * Compare quote items
 * @since 2.0.0
 */
class Compare
{
    /**
     * @var Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @var JsonValidator
     * @since 2.2.0
     */
    private $jsonValidator;

    /**
     * Constructor
     *
     * @param Json|null $serializer
     * @param JsonValidator|null $jsonValidator
     * @since 2.2.0
     */
    public function __construct(
        Json $serializer = null,
        JsonValidator $jsonValidator = null
    ) {
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->jsonValidator = $jsonValidator ?: ObjectManager::getInstance()->get(JsonValidator::class);
    }

    /**
     * Returns option values adopted to compare
     *
     * @param mixed $value
     * @return mixed
     * @since 2.0.0
     */
    protected function getOptionValues($value)
    {
        if (is_string($value) && $this->jsonValidator->isValid($value)) {
            $value = $this->serializer->unserialize($value);
            if (is_array($value)) {
                unset($value['qty'], $value['uenc']);
                $value = array_filter($value, function ($optionValue) {
                    return !empty($optionValue);
                });
            }
        }
        return $value;
    }

    /**
     * Compare two quote items
     *
     * @param Item $target
     * @param Item $compared
     * @return bool
     * @since 2.0.0
     */
    public function compare(Item $target, Item $compared)
    {
        if ($target->getProductId() != $compared->getProductId()) {
            return false;
        }
        $targetOptions = $this->getOptions($target);
        $comparedOptions = $this->getOptions($compared);

        if (array_diff_key($targetOptions, $comparedOptions) != array_diff_key($comparedOptions, $targetOptions)
        ) {
            return false;
        }
        foreach ($targetOptions as $name => $value) {
            if ($comparedOptions[$name] != $value) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns options adopted to compare
     *
     * @param Item $item
     * @return array
     * @since 2.0.0
     */
    public function getOptions(Item $item)
    {
        $options = [];
        foreach ($item->getOptions() as $option) {
            $options[$option->getCode()] = $this->getOptionValues($option->getValue());
        }
        return $options;
    }
}
