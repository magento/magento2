<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor;

class SimpleConstructor
{
    /**
     * @var int
     */
    private $entityId;

    /**
     * @var string
     */
    private $name;
    /**
     * @var \Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\Simple[]
     */
    private $customers;

    /**
     * @param int $entityId
     * @param string $name
     * @param \Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\Simple[] $customers
     */
    public function __construct(
        int $entityId,
        string $name,
        array $customers = null
    ) {
        $this->entityId = $entityId;
        $this->name = $name;
        $this->customers = $customers;
    }

    /**
     * @param int $entityId
     */
    public function setEntityId(int $entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\Simple[]
     */
    public function getCustomers(): array
    {
        return $this->customers;
    }

    /**
     * @param \Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\Simple[] $customers
     */
    public function setCustomers(array $customers)
    {
        $this->customers = $customers;
    }
}
