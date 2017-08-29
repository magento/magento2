<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Console;

use Symfony\Component\Console\Input\InputOption;

class ImageResizeOptions
{

    /**
     * Key for jobs option
     */
    const JOBS_AMOUNT = 'jobs';

    /**
     * Key for product-offset option
     */
    const PRODUCT_OFFSET = 'product-offset';

    /**
     * Key for product-limit option
     */
    const PRODUCT_LIMIT = 'product-limit';

    /**
     * Default jobs amount
     */
    const DEFAULT_JOBS_AMOUNT = 0;

    /**
     * Default product offset for the number of products processed
     */
    const DEFAULT_PRODUCT_OFFSET = 0;

    /**
     * Default product limit for the number of products processed
     */
    const DEFAULT_PRODUCT_LIMIT = 0;

    /**
     * Image resize command options list
     *
     * @return array
     */
    public function getOptionsList()
    {
        return [
            new InputOption(
                self::JOBS_AMOUNT,
                null,
                InputOption::VALUE_OPTIONAL,
                'Enable parallel processing using the specified number of jobs.',
                self::DEFAULT_JOBS_AMOUNT
            ),
            new InputOption(
                self::PRODUCT_LIMIT,
                null,
                InputOption::VALUE_OPTIONAL,
                'Limit the number of products processed.',
                self::DEFAULT_PRODUCT_LIMIT
            ),
            new InputOption(
                self::PRODUCT_OFFSET,
                null,
                InputOption::VALUE_OPTIONAL,
                'Set the offset for the number of products processed.',
                self::DEFAULT_PRODUCT_OFFSET
            ),
        ];
    }
}
