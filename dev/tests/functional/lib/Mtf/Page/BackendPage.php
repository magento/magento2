<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mtf\Page;

use Mtf\Factory\Factory;

/**
 * Class BackendPage
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
    protected function _init()
    {
        $this->_url = $_ENV['app_backend_url'] . static::MCA;
    }

    /**
     * Open backend page and log in if needed
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
