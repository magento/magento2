<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Test\Unit\Code\Generator;

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
