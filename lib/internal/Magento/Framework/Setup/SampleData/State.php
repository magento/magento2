<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\SampleData;

/**
 * Interface for SampleData modules installation
 */
class State implements StateInterface
{
    /**
     * @inheritdoc
     */
    public function setError()
    {

    }

    /**
     * @inheritdoc
     */
    public function hasError()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function setInstalled()
    {

    }

    /**
     * @inheritdoc
     */
    public function isInstalled()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function clearState()
    {

    }

}
