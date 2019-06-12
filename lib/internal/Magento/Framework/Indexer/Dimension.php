<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer;

/**
 * Index Dimension object
<<<<<<< HEAD
=======
 *
 * @api
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 */
class Dimension
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $name
     * @param string $value
     */
    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Get dimension name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get dimension value
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
