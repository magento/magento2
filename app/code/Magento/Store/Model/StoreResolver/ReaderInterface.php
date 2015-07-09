<?php
/**
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Store\Model\StoreResolver;

interface ReaderInterface
{
    /**
     * Retrieve list of stores available for scope
     *
     * @param string $scopeCode
     * @return array
     */
    public function getAllowedStoreIds($scopeCode);

    /**
     * Retrieve default store id
     *
     * @param string $scopeCode
     * @return int
     */
    public function getDefaultStoreId($scopeCode);
}
