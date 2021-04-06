<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search;

use Magento\CatalogSearch\Model\Search\Request\ModifierInterface;
use Magento\Framework\Config\ReaderInterface;

/**
 * @deprecated 101.0.0
 * @see \Magento\ElasticSearch
 */
class ReaderPlugin
{
    /**
     * @var ModifierInterface
     */
    private $requestModifier;

    /**
     * @param ModifierInterface $requestModifier
     */
    public function __construct(
        ModifierInterface $requestModifier
    ) {
        $this->requestModifier = $requestModifier;
    }

    /**
     * Merge reader's value with generated
     *
     * @param ReaderInterface $subject
     * @param array $result
     * @param string|null $scope
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRead(
        ReaderInterface $subject,
        array $result,
        $scope = null
    ) {
        $result = $this->requestModifier->modify($result);
        return $result;
    }
}
