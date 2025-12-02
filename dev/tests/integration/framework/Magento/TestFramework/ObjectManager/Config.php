<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\TestFramework\ObjectManager;

class Config extends \Magento\Framework\Interception\ObjectManager\Config\Developer
{
    /**
     * Clean configuration
     */
    public function clean()
    {
        $this->_preferences = [];
        $this->_virtualTypes = [];
        $this->_arguments = [];
        $this->_nonShared = [];
        $this->_mergedArguments = [];
    }
}
