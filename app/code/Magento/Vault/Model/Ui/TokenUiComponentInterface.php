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
 */
interface TokenUiComponentInterface
{
    /**
     * Returns component configuration
     *
     * @return array
     */
    public function getConfig();

    /**
     * Returns component name
     *
     * @return string
     */
    public function getName();
}
