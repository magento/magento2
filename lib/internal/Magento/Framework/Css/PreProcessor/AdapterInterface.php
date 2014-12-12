<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Css\PreProcessor;

/**
 * Css pre-processor adapter interface
 */
interface AdapterInterface
{
    /**
     * @param string $sourceFilePath
     * @return string
     */
    public function process($sourceFilePath);
}
