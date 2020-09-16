<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search;

/**
 * @deprecated 101.0.0
 * @see \Magento\ElasticSearch
 */
class ReaderPlugin
{
    /**
     * @var \Magento\CatalogSearch\Model\Search\RequestGenerator
     */
    private $requestGenerator;

    /**
     * @param \Magento\CatalogSearch\Model\Search\RequestGenerator $requestGenerator
     */
    public function __construct(
        \Magento\CatalogSearch\Model\Search\RequestGenerator $requestGenerator
    ) {
        $this->requestGenerator = $requestGenerator;
    }

    /**
     * Merge reader's value with generated
     *
     * @param \Magento\Framework\Config\ReaderInterface $subject
     * @param array $result
     * @param string|null $scope
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRead(
        \Magento\Framework\Config\ReaderInterface $subject,
        array $result,
        $scope = null
    ) {
        $result = array_merge_recursive($result, $this->requestGenerator->generate());
        return $result;
    }
}
