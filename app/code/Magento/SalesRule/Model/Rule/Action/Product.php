<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action;

/**
 * Class \Magento\SalesRule\Model\Rule\Action\Product
 *
 * @since 2.0.0
 */
class Product extends \Magento\Rule\Model\Action\AbstractAction
{
    /**
     * Load attribute options
     *
     * @return $this
     * @since 2.0.0
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(['rule_price' => __('Special Price')]);
        return $this;
    }

    /**
     * Load operator options
     *
     * @return $this
     * @since 2.0.0
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            [
                'to_fixed' => __('To Fixed Value'),
                'to_percent' => __('To Percentage'),
                'by_fixed' => __('By Fixed value'),
                'by_percent' => __('By Percentage'),
            ]
        );
        return $this;
    }

    /**
     * Return html
     *
     * @return string
     * @since 2.0.0
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() . __(
            "Update product's %1 %2: %3",
            $this->getAttributeElement()->getHtml(),
            $this->getOperatorElement()->getHtml(),
            $this->getValueElement()->getHtml()
        );
        $html .= $this->getRemoveLinkHtml();
        return $html;
    }
}
