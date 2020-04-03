<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Setup;

use Magento\Framework\Exception\InputException;

/**
 * Configure search engine from installation input
 */
interface InstallConfigInterface
{
    /**
     * Configure search engine based in input options
     *
     * @param array $inputOptions
     * @throws InputException
     */
    public function configure(array $inputOptions);
}
