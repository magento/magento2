<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Router\ActionList;

class Reader
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $this->moduleReader = $moduleReader;
    }

    /**
     * Read list of all available application actions
     *
     * @return array
     */
    public function read()
    {
        $actionFiles = $this->moduleReader->getActionFiles();
        $actions = [];
        foreach ($actionFiles as $actionFile) {
            $action = str_replace('/', '\\', substr($actionFile, 0, -4));
            $actions[strtolower($action)] = $action;
        }
        return $actions;
    }
}
