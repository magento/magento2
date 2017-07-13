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
 */
interface CardRendererInterface extends TokenRendererInterface, IconInterface
{
    /**
     * @return string
     */
    public function getNumberLast4Digits();

    /**
     * @return string
     */
    public function getExpDate();
}
