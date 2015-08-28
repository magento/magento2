<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor;

/**
 * Css pre-processor adapter interface
 */
interface AdapterInterface
{
    const ERROR_MESSAGE_PREFIX = 'CSS compilation from source ';

    /**
     * @param string $sourceFilePath
     * @return string
     */
    public function process($sourceFilePath);
}
