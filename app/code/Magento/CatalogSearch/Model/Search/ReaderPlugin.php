<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
