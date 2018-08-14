<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler\Config;

class ModificationChain implements ModificationInterface
{
    /**
     * @var ModificationInterface[]
     */
    private $modificationsList;

    /**
     * @param array $modificationsList
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
