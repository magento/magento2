<?php
// GENERATED CODE -- DO NOT EDIT!

// Original file comments:
// *
// Copyright © Magento, Inc. All rights reserved.
// See COPYING.txt for license details.
namespace Magento\SearchStorefrontApi\Proto;

/**
 */
class SearchClient extends \Grpc\BaseStub
{

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null)
    {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Magento\SearchStorefrontApi\Proto\ProductSearchRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function searchProducts(
        \Magento\SearchStorefrontApi\Proto\ProductSearchRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.searchStorefrontApi.proto.Search/searchProducts',
            $argument,
            ['\Magento\SearchStorefrontApi\Proto\ProductsSearchResult', 'decode'],
            $metadata,
            $options
        );
    }
}
