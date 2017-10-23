<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

class Autocomplete implements AutocompleteInterface
{
    /**
     * @var Autocomplete\DataProviderInterface[]
     */
    private $dataProviders;

    /**
     * @param array $dataProviders
     */
    public function __construct(
        array $dataProviders
    ) {
        $this->dataProviders = $dataProviders;
        ksort($this->dataProviders);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $data = [];
        foreach ($this->dataProviders as $dataProvider) {
            $data = array_merge($data, $dataProvider->getItems());
        }

        return $data;
    }
}
