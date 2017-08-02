<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\SampleData;

/**
 * Interface for SampleData modules installation
 * @since 2.0.0
 */
interface StateInterface
{
    /**
     * Current state
     */
    const ERROR = 'error';
    const INSTALLED = 'installed';

    /**
     * Set error flag to Sample Data state
     *
     * @return void
     * @since 2.0.0
     */
    public function setError();

    /**
     * Check if Sample Data state has error
     *
     * @return bool
     * @since 2.0.0
     */
    public function hasError();

    /**
     * Set installed flag to Sample Data state
     *
     * @return void
     * @since 2.0.0
     */
    public function setInstalled();

    /**
     * Check if Sample Data is installed
     *
     * @return bool
     * @since 2.0.0
     */
    public function isInstalled();

    /**
     * Clear Sample Data state
     *
     * @return void
     * @since 2.0.0
     */
    public function clearState();
}
