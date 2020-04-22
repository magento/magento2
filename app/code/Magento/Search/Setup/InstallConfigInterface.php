<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Setup;

/**
 * Configure search engine from installation input
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
