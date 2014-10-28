<?php
/**
 *
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
namespace Magento\Downloadable\Service\V1\DownloadableSample;

use \Magento\Downloadable\Service\V1\DownloadableSample\Data\DownloadableSampleContent;

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
