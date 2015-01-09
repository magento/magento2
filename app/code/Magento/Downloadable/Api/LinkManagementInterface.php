<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Downloadable\Api;

use Magento\Downloadable\Api\Data\linkContentInterface;

interface LinkManagementInterface
{
    /**
     * List of samples for downloadable product
     *
     * @param string $productSku
     * @return \Magento\Downloadable\Api\Data\SampleInterface[]
     */
    public function getSamples($productSku);

    /**
     * List of links with associated samples
     *
     * @param string $productSku
     * @return \Magento\Downloadable\Api\Data\LinkInterface[]
     */
    public function getLinks($productSku);

    /**
     * Add downloadable link to the given product
     *
     * @param string $productSku
     * @param \Magento\Downloadable\Api\Data\LinkContentInterface $linkContent
     * @param bool $isGlobalScopeContent
     * @return int link ID
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function create($productSku, LinkContentInterface $linkContent, $isGlobalScopeContent = false);

    /**
     * Update downloadable link of the given product (link type and its resources cannot be changed)
     *
     * @param string $productSku
     * @param int $linkId
     * @param \Magento\Downloadable\Api\Data\LinkContentInterface $linkContent
     * @param bool $isGlobalScopeContent
     * @return bool
     */
    public function update($productSku, $linkId, LinkContentInterface $linkContent, $isGlobalScopeContent = false);


    /**
     * Delete downloadable link
     *
     * @param int $linkId
     * @return bool
     */
    public function delete($linkId);
}
