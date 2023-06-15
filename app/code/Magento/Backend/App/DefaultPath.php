<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App;

/**
 * Default application path for backend area
 *
 * @api
 * @since 100.0.2
 */
class DefaultPath implements \Magento\Framework\App\DefaultPathInterface
{
    /**
     * @var array
     */
    protected $_parts;

    /**
     * @param \Magento\Backend\App\ConfigInterface $config
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(\Magento\Backend\App\ConfigInterface $config)
    {
        $pathConfigValue = $config->getValue('web/default/admin') ?? '';
        $pathParts  = [];
        if ($pathConfigValue) {
            $pathParts = explode('/', $pathConfigValue);
        }

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
     */
    public function getPart($code)
    {
        return $this->_parts[$code] ?? null;
    }
}
