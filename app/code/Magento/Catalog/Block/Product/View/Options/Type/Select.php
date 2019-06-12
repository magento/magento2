<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Product\View\Options\Type;

use Magento\Catalog\Model\Product\Option;
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
    public function getValuesHtml()
    {
        $option = $this->getOption();
        $optionType = $option->getType();
        if ($optionType === Option::OPTION_TYPE_DROP_DOWN ||
            $optionType === Option::OPTION_TYPE_MULTIPLE
        ) {
<<<<<<< HEAD
            $require = $_option->getIsRequire() ? ' required' : '';
            $extraParams = '';
            $select = $this->getLayout()->createBlock(
                \Magento\Framework\View\Element\Html\Select::class
            )->setData(
                [
                    'id' => 'select_' . $_option->getId(),
                    'class' => $require . ' product-custom-option admin__control-select'
                ]
            );
            if ($_option->getType() == \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN) {
                $select->setName('options[' . $_option->getId() . ']')->addOption('', __('-- Please Select --'));
            } else {
                $select->setName('options[' . $_option->getId() . '][]');
                $select->setClass('multiselect admin__control-multiselect' . $require . ' product-custom-option');
            }
            foreach ($_option->getValues() as $_value) {
                $priceStr = $this->_formatPrice(
                    [
                        'is_percent' => $_value->getPriceType() == 'percent',
                        'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                    ],
                    false
                );
                $select->addOption(
                    $_value->getOptionTypeId(),
                    $_value->getTitle() . ' ' . strip_tags($priceStr) . '',
                    ['price' => $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false)]
                );
            }
            if ($_option->getType() == \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE) {
                $extraParams = ' multiple="multiple"';
            }
            if (!$this->getSkipJsReloadPrice()) {
                $extraParams .= ' onchange="opConfig.reloadPrice()"';
            }
            $extraParams .= ' data-selector="' . $select->getName() . '"';
            $select->setExtraParams($extraParams);

            if ($configValue) {
                $select->setValue($configValue);
            }

            return $select->getHtml();
=======
            $optionBlock = $this->multipleFactory->create();
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        }
        if ($optionType === Option::OPTION_TYPE_RADIO ||
            $optionType === Option::OPTION_TYPE_CHECKBOX
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
