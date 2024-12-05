<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Address\AbstractAddress;

use Magento\Directory\Model\Country;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Cache of Country Models
 */
class CountryModelsCache implements ResetAfterRequestInterface
{
    /** @var array */
    private array $countryModels = [];

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->countryModels = [];
    }

    /**
     * Adds model to cache using key
     *
     * @param string $key
     * @param Country $model
     * @return void
     */
    public function add(string $key, Country $model) : void
    {
        $this->countryModels[$key] = $model;
    }

    /**
     * Gets model from cache using key
     *
     * @param string $key
     * @return Country|null
     */
    public function get(string $key) : ?Country
    {
        return $this->countryModels[$key] ?? null;
    }
}
