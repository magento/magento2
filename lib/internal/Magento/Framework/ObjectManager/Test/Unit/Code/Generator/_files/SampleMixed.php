<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Code\Generator;

/**
 * Class SampleMixed for Proxy and Factory generation with mixed type
 */
class SampleMixed
{
    /**
     * @var mixed
     */
    protected mixed $mixed = null;

    /**
     * @param mixed $mixed
     * @return void
     */
    public function setMixed(mixed $mixed = null): void
    {
        $this->mixed = $mixed;
    }

    /**
     * @return mixed
     */
    public function getMixed(): mixed
    {
        return $this->mixed;
    }
}
