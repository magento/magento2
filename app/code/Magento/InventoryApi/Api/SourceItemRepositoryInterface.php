<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api;

/**
 * In Magento 2 Repository considered as an implementation of Facade pattern which provides a simplified interface
 * to a larger body of code responsible for Domain Entity management
 *
 * The main intention is to make API more readable and reduce dependencies of business logic code on the inner workings
 * of a module, since most code uses the facade, thus allowing more flexibility in developing the system
 *
 * Along with this such approach helps to segregate two responsibilities:
 * 1. Repository now could be considered as an API - Interface for usage (calling) in the business logic
 * 2. Separate class-commands to which Repository proxies initial call (like, Get Save GetList Delete) could be
 *    considered as SPI - Interfaces that you should extend and implement to customize current behaviour
 *
 * The method save is absent, due to different semantic (save multiple)
 * @see SourceItemsSaveInterface
 *
 * There is no get method because SourceItem identifies by compound identifier (sku and source_code),
 * so need to use getList() method
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface SourceItemRepositoryInterface
{
    /**
     * Find SourceItems by SearchCriteria
     *
     * We need to have this method for direct work with SourceItems because this object contains
     * additional data like as qty, status (for example can be searchable by additional field)
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ): \Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface;
}
