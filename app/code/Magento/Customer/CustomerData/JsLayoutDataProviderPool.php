<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\CustomerData;

/**
 * Js layout data provider pool
 *
 * @api
 */
class JsLayoutDataProviderPool implements JsLayoutDataProviderPoolInterface
{
    /**
     * Js layout data providers
     *
     * @var JsLayoutDataProviderInterface[]
     */
    protected $jsLayoutDataProviders;

    /**
     * Construct
     *
     * @param JsLayoutDataProviderInterface[] $jsLayoutDataProviders
     */
    public function __construct(
        array $jsLayoutDataProviders = []
    ) {
        $this->jsLayoutDataProviders = $jsLayoutDataProviders;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [];
        if ($this->jsLayoutDataProviders) {
            foreach ($this->jsLayoutDataProviders as $dataProvider) {
                $data = array_merge_recursive($data, $dataProvider->getData());
            }
        }
        return $data;
    }
}
