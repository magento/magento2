<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Bootstrap;

class MemoryFactory
{
    /**
     * @var \Magento\Framework\Shell
     */
    private $shell;

    /**
     * @param \Magento\Framework\Shell $shell
     */
    public function __construct(\Magento\Framework\Shell $shell)
    {
        $this->shell = $shell;
    }

    /**
     * @param string $memUsageLimit
     * @param string $memLeakLimit
     * @return Memory
     */
    public function create($memUsageLimit, $memLeakLimit)
    {
        return new \Magento\TestFramework\Bootstrap\Memory(
            new \Magento\TestFramework\MemoryLimit(
                $memUsageLimit,
                $memLeakLimit,
                new \Magento\TestFramework\Helper\Memory($this->shell)
            )
        );
    }
}
