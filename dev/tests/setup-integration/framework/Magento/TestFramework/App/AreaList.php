<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\App;

use Magento\Framework\App\Area\FrontNameResolverFactory;
use Magento\Framework\ObjectManagerInterface;

/**
 * Stub for \Magento\Framework\App\AreaList
 */
class AreaList extends \Magento\Framework\App\AreaList
{
    /**
     * @param ObjectManagerInterface $objectManager
     * @param FrontNameResolverFactory $resolverFactory
     * @param array $areas
     * @param string|null $default
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        FrontNameResolverFactory $resolverFactory,
        array $areas = [],
        $default = null
    ) {
        parent::__construct($objectManager, $resolverFactory, $areas, $default);
        /**
         * Then Magento is installed for setup-integration tests, di.xml files are parsed from all Magento modules,
         * causing Magento\Framework\App\AreaList _areas property to be filled with arguments from disabled modules.
         */
        $this->_areas = [];
    }
}
