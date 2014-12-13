<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Api\Code\Generator;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for ExtensibleSample
 */
interface ExtensibleSampleInterface extends ExtensibleDataInterface
{
    /**
     * @return array
     */
    public function getItems();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return int
     */
    public function getCount();

    /**
     * @return int
     */
    public function getCreatedAt();
}
