<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldApi\Api\Data;

/**
 * Extra comment types interface.
 */
interface ApprovedTypesInterface
{
    /**
     * Types of approve.
     */
    const NO = 0;
    const YES = 1;

    /**
     * Return true if template type eq YES.
     *
     * @return boolean
     */
    public function isPlain();

    /**
     * Getter for comment type.
     *
     * @return int
     */
    public function getType();
}
