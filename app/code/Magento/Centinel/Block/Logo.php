<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Centinel payment form logo block
 */
namespace Magento\Centinel\Block;

class Logo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'logo.phtml';

    /**
     * Return code of payment method
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getMethod()->getCode();
    }
}
