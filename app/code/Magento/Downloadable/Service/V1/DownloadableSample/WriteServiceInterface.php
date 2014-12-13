<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Downloadable\Service\V1\DownloadableSample;

use Magento\Downloadable\Service\V1\DownloadableSample\Data\DownloadableSampleContent;

interface WriteServiceInterface
{
    /**
     * Add downloadable sample to the given product
     *
     * @param string $productSku
     * @param \Magento\Downloadable\Service\V1\DownloadableSample\Data\DownloadableSampleContent $sampleContent
     * @param bool $isGlobalScopeContent
     * @return int sample ID
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function create($productSku, DownloadableSampleContent $sampleContent, $isGlobalScopeContent = false);

    /**
     * Update downloadable sample of the given product (sample type and its resource cannot be changed)
     *
     * @param string $productSku
     * @param int $sampleId
     * @param \Magento\Downloadable\Service\V1\DownloadableSample\Data\DownloadableSampleContent $sampleContent
     * @param bool $isGlobalScopeContent
     * @return bool
     */
    public function update(
        $productSku,
        $sampleId,
        DownloadableSampleContent $sampleContent,
        $isGlobalScopeContent = false
    );

    /**
     * Delete downloadable sample
     *
     * @param int $sampleId
     * @return bool
     */
    public function delete($sampleId);
}
