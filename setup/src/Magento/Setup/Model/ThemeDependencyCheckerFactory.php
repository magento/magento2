<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Theme\Model\Theme\ThemeDependencyChecker;

class ThemeDependencyCheckerFactory
{
    /**
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider)
    {
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * Creates ThemeDependencyChecker object
     *
     * @return ThemeDependencyChecker
     */
    public function create()
    {
        $this->objectManagerProvider->get('Magento\Theme\Model\Theme\ThemeDependencyChecker');
    }
}
