<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

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
