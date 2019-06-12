<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\ReportXml;

<<<<<<< HEAD
use Magento\Framework\Module\Manager as ModuleManager;

/**
 * Class ModuleIterator
=======
use \Magento\Framework\Module\ModuleManagerInterface as ModuleManager;

/**
 * Iterator for ReportXml modules
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
