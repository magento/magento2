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

use Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;

/**
 * Data builder class for url rewrite
 */
class UrlRewriteBuilder extends AbstractExtensibleObjectBuilder
{
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
     * @param int $entityType
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
     * @param string $targetPath
     *
     * @return $this
     */
    public function setTargetPath($targetPath)
    {
        return $this->_set(UrlRewrite::TARGET_PATH, $targetPath);
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
     * @param int $redirectCode
     *
     * @return $this
     */
    public function setRedirectCode($redirectCode)
    {
        return $this->_set(UrlRewrite::REDIRECT_TYPE, $redirectCode);
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        return $this->_set(UrlRewrite::DESCRIPTION, $description);
    }
}
