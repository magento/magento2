<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Cart\BuyRequest;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\Stdlib\ArrayManagerFactory;
use Magento\QuoteGraphQl\Model\Cart\BuyRequest\BuyRequestDataProviderInterface;

/**
 * Data provider for bundle product buy requests
 */
class BundleDataProvider implements BuyRequestDataProviderInterface
{
    /**
     * @var ArrayManagerFactory
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly ArrayManagerFactory $arrayManagerFactory;

    /**
     * @param ArrayManager $arrayManager @deprecated @see $arrayManagerFactory
     * @param ArrayManagerFactory|null $arrayManagerFactory
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ArrayManager $arrayManager,
        ?ArrayManagerFactory $arrayManagerFactory = null,
    ) {
        $this->arrayManagerFactory = $arrayManagerFactory
            ?? ObjectManager::getInstance()->get(ArrayManagerFactory::class);
    }

    /**
     * @inheritdoc
     */
    public function execute(array $cartItemData): array
    {
        $bundleOptions = [];
        $bundleInputs = $this->arrayManagerFactory->create()->get('bundle_options', $cartItemData) ?? [];
        foreach ($bundleInputs as $bundleInput) {
            $bundleOptions['bundle_option'][$bundleInput['id']] = $bundleInput['value'];
            $bundleOptions['bundle_option_qty'][$bundleInput['id']] = $bundleInput['quantity'];
        }

        return $bundleOptions;
    }
}
