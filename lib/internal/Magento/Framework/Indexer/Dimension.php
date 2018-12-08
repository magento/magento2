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
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
