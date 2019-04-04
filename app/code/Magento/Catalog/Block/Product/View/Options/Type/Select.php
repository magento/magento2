<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Product\View\Options\Type;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Block\Product\View\Options\Type\Select\CheckableFactory;
use Magento\Catalog\Block\Product\View\Options\Type\Select\MultipleFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Catalog\Helper\Data as CatalogHelper;

/**
 * Product options text type block
 *
 * @api
 * @since 100.0.2
 */
class Select extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    /**
     * @var CheckableFactory
     */
    private $checkableFactory;

    /**
     * @var MultipleFactory
     */
    private $multipleFactory;

    /**
     * Select constructor.
     * @param Context $context
     * @param Data $pricingHelper
     * @param CatalogHelper $catalogData
     * @param array $data
     * @param CheckableFactory|null $checkableFactory
     * @param MultipleFactory|null $multipleFactory
     */
    public function __construct(
        Context $context,
        Data $pricingHelper,
        CatalogHelper $catalogData,
        array $data = [],
        CheckableFactory $checkableFactory = null,
        MultipleFactory $multipleFactory = null
    ) {
        parent::__construct($context, $pricingHelper, $catalogData, $data);
        $this->checkableFactory = $checkableFactory ?: ObjectManager::getInstance()->get(CheckableFactory::class);
        $this->multipleFactory = $multipleFactory ?: ObjectManager::getInstance()->get(MultipleFactory::class);
    }

    /**
     * Return html for control element
     *
     * @return string
     */
    public function getValuesHtml(): string
    {
        $option = $this->getOption();
        $optionType = $option->getType();

        if ($optionType === ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN ||
            $optionType === ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE
        ) {
            $optionBlock = $this->multipleFactory->create();
        }

        if ($optionType === ProductCustomOptionInterface::OPTION_TYPE_RADIO ||
            $optionType === ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX
        ) {
            $optionBlock = $this->checkableFactory->create();
        }

        return $optionBlock
            ->setOption($option)
            ->setProduct($this->getProduct())
            ->setSkipJsReloadPrice(1)
            ->_toHtml();
    }
}
