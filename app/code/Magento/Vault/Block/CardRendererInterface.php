<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block;

use Magento\Vault\Block\Customer\IconInterface;

/**
 * Interface CardRendererInterface
 * @api
 * @since 2.1.0
 */
interface CardRendererInterface extends TokenRendererInterface, IconInterface
{
    /**
     * @return string
     * @since 2.1.0
     */
    public function getNumberLast4Digits();

    /**
     * @return string
     * @since 2.1.0
     */
    public function getExpDate();
}
