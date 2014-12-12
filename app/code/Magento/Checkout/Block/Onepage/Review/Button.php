<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Block\Onepage\Review;

use Magento\Framework\View\Element\Template;

/**
 * One page checkout order review button
 */
class Button extends Template
{
    /**
     * {@inheritdoc}
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        if (!empty($template)) {
            parent::setTemplate($template);
        }
        return $this;
    }
}
