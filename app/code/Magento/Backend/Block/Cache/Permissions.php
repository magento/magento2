<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Cache;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class Permissions
 */
class Permissions implements ArgumentInterface
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * Permissions constructor.
     *
     * @param AuthorizationInterface $authorization
     */
    public function __construct(AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * @return bool
     */
    public function hasAccessToFlushCatalogImages()
    {
        return $this->authorization->isAllowed('Magento_Backend::flush_catalog_images');
    }
    /**
     * @return bool
     */
    public function hasAccessToFlushJsCss()
    {
        return $this->authorization->isAllowed('Magento_Backend::flush_js_css');
    }
    /**
     * @return bool
     */
    public function hasAccessToFlushStaticFiles()
    {
        return $this->authorization->isAllowed('Magento_Backend::flush_static_files');
    }
    /**
     * @return bool
     */
    public function hasAccessToAdditionalActions()
    {
        return ($this->hasAccessToFlushCatalogImages()
                || $this->hasAccessToFlushJsCss()
                || $this->hasAccessToFlushStaticFiles());
    }
}
