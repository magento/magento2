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
 * @since 2.0.0
 */
class JsLayoutDataProviderPool implements JsLayoutDataProviderPoolInterface
{
    /**
     * Js layout data providers
     *
     * @var JsLayoutDataProviderInterface[]
     * @since 2.0.0
     */
    protected $jsLayoutDataProviders;

    /**
     * Construct
     *
     * @param JsLayoutDataProviderInterface[] $jsLayoutDataProviders
     * @since 2.0.0
     */
    public function __construct(
        array $jsLayoutDataProviders = []
    ) {
        $this->jsLayoutDataProviders = $jsLayoutDataProviders;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
