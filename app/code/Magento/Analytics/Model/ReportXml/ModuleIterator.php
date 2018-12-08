<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\ReportXml;

use Magento\Framework\Module\Manager as ModuleManager;

/**
<<<<<<< HEAD
 * Class ModuleIterator
=======
 * Iterator for ReportXml modules
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
class ModuleIterator extends \IteratorIterator
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
<<<<<<< HEAD
     * ModuleIterator constructor.
     *
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
