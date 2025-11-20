<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Test\Unit\Helper;

use Magento\GraphQl\Model\Query\ContextExtension;
use Magento\Store\Api\Data\StoreInterface;

class ContextExtensionGetStoreTestHelper extends ContextExtension
{
    /**
     * @var StoreInterface|null
     */
    private $store;

    /**
     * @return StoreInterface|null
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @param StoreInterface $store
     * @return $this
     */
    public function setStore(StoreInterface $store)
    {
        $this->store = $store;
        return $this;
    }
}
