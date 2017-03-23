<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block\Transparent;

/**
 * Class Info. Payment Information block used for transparent redirect feature
 * @package Magento\Payment\Block\Transparent
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Payment::transparent/info.phtml';
}
