<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api;

use Magento\Downloadable\Api\Data\SampleInterface;

interface SampleRepositoryInterface
{
    /**
     * Update downloadable sample of the given product
     *
     * @param string $productSku
     * @param \Magento\Downloadable\Api\Data\SampleInterface $sample
     * @param bool $isGlobalScopeContent
     * @return int
     */
    public function save(
        $productSku,
        SampleInterface $sample,
        $isGlobalScopeContent = false
    );

    /**
     * Delete downloadable sample
     *
     * @param int $id
     * @return bool
     */
    public function delete($id);
}
