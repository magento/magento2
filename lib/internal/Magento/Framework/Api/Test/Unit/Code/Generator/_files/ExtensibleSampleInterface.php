<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
