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
namespace Magento\UrlRedirect\Service\V1\Data;

/**
 * Url rewrite search filter
 */
class Filter
{
    /**
     * Data with filter values
     *
     * @var array
     */
    protected $data = [];

    /**
     * Possible fields for filter
     *
     * @var array
     */
    protected $possibleFields = [
        UrlRewrite::ENTITY_ID,
        UrlRewrite::ENTITY_TYPE,
        UrlRewrite::STORE_ID,
        UrlRewrite::REQUEST_PATH,
        UrlRewrite::REDIRECT_TYPE,
    ];

    /**
     * Filter constructor
     *
     * @param array $filterData
     * @throws \InvalidArgumentException
     */
    public function __construct(array $filterData = [])
    {
        if ($filterData) {
            if ($wrongFields = array_diff(array_keys($filterData), $this->possibleFields)) {
                throw new \InvalidArgumentException(
                    sprintf('There is wrong fields passed to filter: "%s"', implode(', ', $wrongFields))
                );
            }
            $this->data = $filterData;
        }
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    protected function _set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilter()
    {
        return $this->data;
    }

    /**
     * @param int $entityId
     *
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->_set(UrlRewrite::ENTITY_ID, $entityId);
    }

    /**
     * @param int|array $entityType
     *
     * @return $this
     */
    public function setEntityType($entityType)
    {
        return $this->_set(UrlRewrite::ENTITY_TYPE, $entityType);
    }

    /**
     * @param string $requestPath
     *
     * @return $this
     */
    public function setRequestPath($requestPath)
    {
        return $this->_set(UrlRewrite::REQUEST_PATH, $requestPath);
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->_set(UrlRewrite::STORE_ID, $storeId);
    }

    /**
     * @param string|array $redirectType
     *
     * @return $this
     */
    public function setRedirectType($redirectType)
    {
        return $this->_set(UrlRewrite::REDIRECT_TYPE, $redirectType);
    }
}
