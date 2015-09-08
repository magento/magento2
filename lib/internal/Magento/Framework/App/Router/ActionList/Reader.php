<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Router\ActionList;

use Magento\Framework\Module\Dir\Reader as ModuleReader;

class Reader
{
    /**
     * @var ModuleReader
     */
    protected $moduleReader;

    /**
     * @param ModuleReader $moduleReader
     */
    public function __construct(ModuleReader $moduleReader)
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
        return $actionFiles;
    }
}
