<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Ui;

/**
 * Interface TokenUiComponentInterface
 * @package Magento\Vault\Model\Ui
 * @api
 * @since 2.1.0
 * @since 100.1.0
 */
interface TokenUiComponentInterface
{
    /**
     * Returns component configuration
     *
     * @return array
     * @since 2.1.0
     * @since 100.1.0
     */
    public function getConfig();

    /**
     * Returns component name
     *
     * @return string
     * @since 2.1.0
     * @since 100.1.0
     */
    public function getName();
}
