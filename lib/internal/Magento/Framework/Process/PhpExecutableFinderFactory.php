<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Process;

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
