<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Fixture;

interface DataObjectInterface
{
    /**
     * Set A
     *
     * @param string $value
     *
     * @return void
     */
    public function setFirstASecond(string $value): void;

    /**
     * Set At
     *
     * @param string $value
     *
     * @return void
     */
    public function setFirstAtSecond(string $value): void;

    /**
     * Set ATM
     *
     * @param string $value
     *
     * @return void
     */
    public function setFirstATMSecond(string $value): void;
}
