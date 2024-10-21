<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\BuyRequest;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\Stdlib\ArrayManagerFactory;

/**
 * Extract buy request elements require for custom options
 */
class CustomizableOptionsDataProvider implements BuyRequestDataProviderInterface
{
    /**
     * @var ArrayManagerFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly ArrayManagerFactory $arrayManagerFactory;

    /**
     * @param ArrayManager $arrayManager @deprecated @see $arrayManagerFactory
     * @param ArrayManagerFactory|null $arrayManagerFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ArrayManager $arrayManager,
        ArrayManagerFactory $arrayManagerFactory = null
    ) {
        $this->arrayManagerFactory = $arrayManagerFactory
            ?? ObjectManager::getInstance()->get(ArrayManagerFactory::class);
    }

    /**
     * @inheritdoc
     */
    public function execute(array $cartItemData): array
    {
        $customizableOptions = $this->arrayManagerFactory->create()->get('customizable_options', $cartItemData, []);
        $customizableOptionsData = [];
        foreach ($customizableOptions as $customizableOption) {
            if (isset($customizableOption['value_string'])) {
                $customizableOptionsData[$customizableOption['id']] = $this->convertCustomOptionValue(
                    $customizableOption['value_string']
                );
            }
        }
        return ['options' => $customizableOptionsData];
    }

    /**
     * Convert custom options value
     *
     * @param string $value
     * @return string|array
     */
    private function convertCustomOptionValue(string $value)
    {
        $value = trim($value);
        if (substr($value, 0, 1) === "[" &&
            substr($value, strlen($value) - 1, 1) === "]") {
            return explode(',', substr($value, 1, -1));
        }
        return $value;
    }
}
