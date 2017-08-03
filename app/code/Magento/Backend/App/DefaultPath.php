<?php
/**
 * Default application path for backend area
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App;

/**
 * @api
 * @since 2.0.0
 */
class DefaultPath implements \Magento\Framework\App\DefaultPathInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_parts;

    /**
     * @param \Magento\Backend\App\ConfigInterface $config
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function __construct(\Magento\Backend\App\ConfigInterface $config)
    {
        $pathParts = explode('/', $config->getValue('web/default/admin'));

        $this->_parts = [
            'area' => isset($pathParts[0]) ? $pathParts[0] : '',
            'module' => isset($pathParts[1]) ? $pathParts[1] : 'admin',
            'controller' => isset($pathParts[2]) ? $pathParts[2] : 'index',
            'action' => isset($pathParts[3]) ? $pathParts[3] : 'index',
        ];
    }

    /**
     * Retrieve default path part by code
     *
     * @param string $code
     * @return string
     * @since 2.0.0
     */
    public function getPart($code)
    {
        return isset($this->_parts[$code]) ? $this->_parts[$code] : null;
    }
}
