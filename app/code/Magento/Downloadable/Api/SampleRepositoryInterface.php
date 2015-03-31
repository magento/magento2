<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api;

use Magento\Downloadable\Api\Data\SampleContentInterface;

interface SampleRepositoryInterface
{
    /**
     * Update downloadable sample of the given product (sample type and its resource cannot be changed)
     *
     * @param string $productSku
     * @param \Magento\Downloadable\Api\Data\SampleContentInterface $sampleContent
     * @param int $sampleId
     * @param bool $isGlobalScopeContent
     * @return int
     */
    public function save(
        $productSku,
        SampleContentInterface $sampleContent,
        $sampleId = null,
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
