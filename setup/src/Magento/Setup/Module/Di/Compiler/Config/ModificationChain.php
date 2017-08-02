<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler\Config;

/**
 * Class \Magento\Setup\Module\Di\Compiler\Config\ModificationChain
 *
 * @since 2.0.0
 */
class ModificationChain implements ModificationInterface
{
    /**
     * @var ModificationInterface[]
     * @since 2.0.0
     */
    private $modificationsList;

    /**
     * @param array $modificationsList
     * @since 2.0.0
     */
    public function __construct(array $modificationsList = [])
    {
        array_walk(
            $modificationsList,
            function ($modification) {
                if (!$modification instanceof ModificationInterface) {
                    throw new \InvalidArgumentException('Wrong modifier provided');
                }
            }
        );
        $this->modificationsList = $modificationsList;
    }

    /**
     * Modifies input config
     *
     * @param array $config
     * @return array
     * @since 2.0.0
     */
    public function modify(array $config)
    {
        $outputConfig = $config;
        foreach ($this->modificationsList as $modification) {
            $outputConfig = $modification->modify($outputConfig);
        }
        return $outputConfig;
    }
}
