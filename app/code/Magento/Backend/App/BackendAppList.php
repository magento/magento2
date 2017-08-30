<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\App;

/**
 * List of Backend Applications to allow injection of them through the DI
 * @api
 */
class BackendAppList
{
    /**
     * @var BackendApp[]
     */
    private $backendApps = [];

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $backendApps
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        array $backendApps = []
    ) {
        $this->backendApps = $backendApps;
        $this->request = $request;
    }

    /**
     * Get Backend app based on its name
     *
     * @return BackendApp|null
     */
    public function getCurrentApp()
    {
        $appName = $this->request->getQuery('app');
        if ($appName && isset($this->backendApps[$appName])) {
            return $this->backendApps[$appName];
        }
        return null;
    }

    /**
     * Retrieve backend application by name
     *
     * @param string $appName
     * @return BackendApp|null
     */
    public function getBackendApp($appName)
    {
        if (isset($this->backendApps[$appName])) {
            return $this->backendApps[$appName];
        }
        return null;
    }
}
