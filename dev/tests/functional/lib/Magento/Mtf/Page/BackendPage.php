<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Mtf\Page;

use Magento\Mtf\Factory\Factory;

/**
 * Admin backend page.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class BackendPage extends Page
{
    /**
     * Init page. Set page url
     *
     * @return void
     */
    protected function initUrl()
    {
        $this->url = $_ENV['app_backend_url'] . static::MCA;
    }

    /**
     * Open backend page and log in if needed.
     *
     * @param array $params
     * @return $this
     */
    public function open(array $params = [])
    {
        Factory::getApp()->magentoBackendLoginUser();
        return parent::open($params);
    }
}
