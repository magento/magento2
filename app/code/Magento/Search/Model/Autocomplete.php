<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

/**
 * Class \Magento\Search\Model\Autocomplete
 *
 * @since 2.0.0
 */
class Autocomplete implements AutocompleteInterface
{
    /**
     * @var Autocomplete\DataProviderInterface[]
     * @since 2.0.0
     */
    private $dataProviders;

    /**
     * @param array $dataProviders
     * @since 2.0.0
     */
    public function __construct(
        array $dataProviders
    ) {
        $this->dataProviders = $dataProviders;
        ksort($this->dataProviders);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
