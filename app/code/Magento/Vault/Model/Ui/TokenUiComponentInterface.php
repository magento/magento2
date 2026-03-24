<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Vault\Model\Ui;

/**
 * Interface TokenUiComponentInterface
 * @package Magento\Vault\Model\Ui
 * @api
 * @since 100.1.0
 */
interface TokenUiComponentInterface
{
    /**
     * Returns component configuration
     *
     * @return array
     * @since 100.1.0
     */
    public function getConfig();

    /**
     * Returns component name
     *
     * @return string
     * @since 100.1.0
     */
    public function getName();
}
