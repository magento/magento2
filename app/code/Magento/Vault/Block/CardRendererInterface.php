<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Vault\Block;

use Magento\Vault\Block\Customer\IconInterface;

/**
 * Interface CardRendererInterface
 * @api
 * @since 100.1.0
 */
interface CardRendererInterface extends TokenRendererInterface, IconInterface
{
    /**
     * @return string
     * @since 100.1.0
     */
    public function getNumberLast4Digits();

    /**
     * @return string
     * @since 100.1.0
     */
    public function getExpDate();
}
