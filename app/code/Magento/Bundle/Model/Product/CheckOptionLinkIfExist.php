<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Api\ProductOptionRepositoryInterface as OptionRepository;
use Magento\Bundle\Model\Link;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Check bundle product option link if exist
 */
class CheckOptionLinkIfExist
{
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * @param OptionRepository $optionRepository
     */
    public function __construct(OptionRepository $optionRepository)
    {
        $this->optionRepository = $optionRepository;
    }

    /**
     * Check if link is already exist in bundle product option
     *
     * @param string $sku
     * @param OptionInterface $optionToDelete
     * @param Link $link
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function execute(string $sku, OptionInterface $optionToDelete, Link $link): bool
    {
        $isLinkExist = true;
        $availableOptions = $this->getAvailableOptionsAfterDelete($sku, $optionToDelete);
        $optionLinkIds = $this->getLinkIds($availableOptions);
        if (in_array($link->getEntityId(), $optionLinkIds)) {
            $isLinkExist = false;
        }
        return $isLinkExist;
    }

    /**
     * Retrieve bundle product options after delete option
     *
     * @param string $sku
     * @param OptionInterface $optionToDelete
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function getAvailableOptionsAfterDelete(string $sku, OptionInterface $optionToDelete): array
    {
        $bundleProductOptions = $this->optionRepository->getList($sku);
        $options = [];
        foreach ($bundleProductOptions as $bundleOption) {
            if ($bundleOption->getOptionId() == $optionToDelete->getOptionId()) {
                continue;
            }
            $options[] = $bundleOption;
        }
        return $options;
    }

    /**
     * Retrieve bundle product link options
     *
     * @param array $options
     * @return array
     */
    private function getLinkIds(array $options): array
    {
        $ids = [];
        foreach ($options as $option) {
            $links = $option->getProductLinks();
            if (!empty($links)) {
                foreach ($links as $link) {
                    $ids[] = $link->getEntityId();
                }
            }
        }
        return $ids;
    }
}
