<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\SampleData;

/**
 * Interface for SampleData modules installation
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
     */
    public function setError();

    /**
     * Check if Sample Data state has error
     *
     * @return bool
     */
    public function hasError();

    /**
     * Set installed flag to Sample Data state
     *
     * @return void
     */
    public function setInstalled();

    /**
     * Check if Sample Data is installed
     *
     * @return bool
     */
    public function isInstalled();

    /**
     * Clear Sample Data state
     *
     * @return void
     */
    public function clearState();
}
