<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action;

class Product extends \Magento\Rule\Model\Action\AbstractAction
{
    /**
     * Load attribute options
     *
     * @return $this
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
