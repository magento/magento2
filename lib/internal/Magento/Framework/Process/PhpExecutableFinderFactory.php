<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Process;

/**
 * Class \Magento\Framework\Process\PhpExecutableFinderFactory
 *
 */
class PhpExecutableFinderFactory
{
    /**
     * Create PhpExecutableFinder instance
     *
     * @return \Symfony\Component\Process\PhpExecutableFinder
     */
    public function create()
    {
        return new \Symfony\Component\Process\PhpExecutableFinder();
    }
}
