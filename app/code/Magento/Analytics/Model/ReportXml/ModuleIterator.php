<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\ReportXml;

use Magento\Framework\Module\Manager as ModuleManager;

/**
 * Class ModuleIterator
 */
class ModuleIterator extends \IteratorIterator
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * ModuleIterator constructor.
     *
     * @param ModuleManager $moduleManager
     * @param \Traversable $iterator
     */
    public function __construct(
        ModuleManager $moduleManager,
        \Traversable $iterator
    ) {
        parent::__construct($iterator);
        $this->moduleManager = $moduleManager;
    }

    /**
     * Returns module with module status
     *
     * @return array
     */
    public function current()
    {
        $current = parent::current();
        if (is_array($current) && isset($current['module_name'])) {
            $current['status'] =
                $this->moduleManager->isEnabled($current['module_name']) == 1 ? 'Enabled' : "Disabled";
        }
        return $current;
    }
}
