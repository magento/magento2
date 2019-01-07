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
        $pathParts = explode('/', $config->getValue('web/default/admin'));

        $this->_parts = [
            'area' => $pathParts[0] ?? '',
            'module' => $pathParts[1] ?? 'admin',
            'controller' => $pathParts[2] ?? 'index',
            'action' => $pathParts[3] ?? 'index',
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
