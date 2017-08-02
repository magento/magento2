<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block\Transparent;

/**
 * Class Info. Payment Information block used for transparent redirect feature
 *
 * @api
 * @since 2.0.0
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Payment::transparent/info.phtml';
}
