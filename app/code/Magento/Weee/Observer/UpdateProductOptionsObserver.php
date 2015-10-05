<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateProductOptionsObserver implements ObserverInterface
{
    /**
     * Weee data
     *
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeData = null;

    /** @var \Magento\Framework\Registry */
    protected $registry;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Weee\Helper\Data $weeeData
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Weee\Helper\Data $weeeData
    ) {
        $this->weeeData = $weeeData;
        $this->registry = $registry;
    }

    /**
     * Change default JavaScript templates for options rendering
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getEvent()->getResponseObject();
        $options = $response->getAdditionalOptions();

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->registry->registry('current_product');
        if (!$product) {
            return $this;
        }

        if ($this->weeeData->isEnabled() &&
            !$this->weeeData->geDisplayIncl($product->getStoreId()) &&
            !$this->weeeData->geDisplayExcl($product->getStoreId())
        ) {
            // only do processing on bundle product
            if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                if (!array_key_exists('optionTemplate', $options)) {
                    $options['optionTemplate'] = '<%- data.label %>'
                        . '<% if (data.finalPrice.value) { %>'
                        . ' +<%- data.finalPrice.formatted %>'
                        . '<% } %>';
                }

                foreach ($this->weeeData->getWeeeAttributesForBundle($product) as $weeeAttributes) {
                    foreach ($weeeAttributes as $weeeAttribute) {
                        if (!preg_match('/'.$weeeAttribute->getCode().'/', $options['optionTemplate'])) {
                            $options['optionTemplate'] .= sprintf(
                                ' <%% if (data.weeePrice' . $weeeAttribute->getCode() . ') { %%>'
                                . '  (' . $weeeAttribute->getName()
                                . ':<%%= data.weeePrice' . $weeeAttribute->getCode()
                                . '.formatted %%>)'
                                . '<%% } %%>'
                            );
                        }
                    }
                }

                if ($this->weeeData->geDisplayExlDescIncl($product->getStoreId())) {
                    $options['optionTemplate'] .= sprintf(
                        ' <%% if (data.weeePrice) { %%>'
                        . '<%%= data.weeePrice.formatted %%>'
                        . '<%% } %%>'
                    );
                }

            }
        }
        $response->setAdditionalOptions($options);
        return $this;
    }
}
