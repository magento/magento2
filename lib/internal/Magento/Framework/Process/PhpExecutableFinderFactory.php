<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Process;

/**
 * Class \Magento\Framework\Process\PhpExecutableFinderFactory
 *
 * @since 2.1.0
 */
class PhpExecutableFinderFactory
{
    /**
     * Create PhpExecutableFinder instance
     *
     * @return \Symfony\Component\Process\PhpExecutableFinder
     * @since 2.1.0
     */
    public function create()
    {
        return new \Symfony\Component\Process\PhpExecutableFinder();
    }
}
