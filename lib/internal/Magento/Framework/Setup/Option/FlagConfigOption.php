<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Option;

/**
 * Flag option in deployment config tool
 */
class FlagConfigOption extends AbstractConfigOption
{
    /**
     * Frontend input types
     */
    const FRONTEND_WIZARD_FLAG = 'flag';

    /**
     * Constructor
     *
     * @param string $name
     * @param string $configPath
     * @param string $description
     * @param string|array|null $shortCut
     */
    public function __construct(
        $name,
        $configPath,
        $description = '',
        $shortCut = null
    ) {
        parent::__construct(
            $name,
            self::FRONTEND_WIZARD_FLAG,
            self::VALUE_NONE,
            $configPath,
            $description,
            null,
            $shortCut
        );
    }
}
