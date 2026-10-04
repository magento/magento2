<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Search\Setup;

/**
 * Configure search engine from installation input
 *
 * @api
 */
interface InstallConfigInterface
{
    /**
     * Configure search engine based in input options
     *
     * @param array $inputOptions
     */
    public function configure(array $inputOptions);
}
