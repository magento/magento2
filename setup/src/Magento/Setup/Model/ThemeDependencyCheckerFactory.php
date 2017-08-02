<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Theme\Model\Theme\ThemeDependencyChecker;

/**
 * Class ThemeDependencyCheckerFactory creates instance of ThemeDependencyChecker
 * @since 2.0.0
 */
class ThemeDependencyCheckerFactory
{
    /**
     * @var ObjectManagerProvider
     * @since 2.0.0
     */
    private $objectManagerProvider;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @since 2.0.0
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider)
    {
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * Creates ThemeDependencyChecker object
     *
     * @return ThemeDependencyChecker
     * @since 2.0.0
     */
    public function create()
    {
        return $this->objectManagerProvider->get()->get(\Magento\Theme\Model\Theme\ThemeDependencyChecker::class);
    }
}
