<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\BuyRequest;

use Magento\Framework\Stdlib\ArrayManager;

/**
 * Extract buy request elements require for custom options
 */
class CustomizableOptionsDataProvider implements BuyRequestDataProviderInterface
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $cartItemData): array
    {
        $customizableOptions = $this->arrayManager->get('customizable_options', $cartItemData, []);

        $customizableOptionsData = [];
        foreach ($customizableOptions as $customizableOption) {
            $customizableOptionsData[$customizableOption['id']] = $customizableOption['value'];
        }

        return ['options' => $customizableOptionsData];
    }
}
