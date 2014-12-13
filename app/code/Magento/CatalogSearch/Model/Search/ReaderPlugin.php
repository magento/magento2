<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogSearch\Model\Search;

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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \Magento\Framework\Config\ReaderInterface $subject
     * @param \Closure $proceed
     * @param string $scope
     * @return array
     */
    public function aroundRead(
        \Magento\Framework\Config\ReaderInterface $subject,
        \Closure $proceed,
        $scope = null
    ) {
        $result = $proceed($scope);
        $result = array_merge_recursive($result, $this->requestGenerator->generate());
        return $result;
    }
}
