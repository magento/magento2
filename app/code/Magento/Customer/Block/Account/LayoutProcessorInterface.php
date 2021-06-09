<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account;

/**
 * Layout processor interface.
 *
 * Can be used to provide a custom logic for customer authentication popup JS layout preparation.
 *
 * @see \Magento\Customer\Block\Account\AuthenticationPopup
 *
 * @api
 * @since 100.0.2
 */
interface LayoutProcessorInterface
{
    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout);
}

