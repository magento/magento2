<?php
/**
 * Default application path for backend area
 *
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
namespace Magento\Backend\App;

class DefaultPath implements \Magento\Framework\App\DefaultPathInterface
{
    /**
     * @var array
     */
    protected $_parts;

    /**
     * @param \Magento\Backend\App\ConfigInterface $config
     */
    public function __construct(\Magento\Backend\App\ConfigInterface $config)
    {
        $pathParts = explode('/', $config->getValue('web/default/admin'));

        $this->_parts = array(
            'area' => isset($pathParts[0]) ? $pathParts[0] : '',
            'module' => isset($pathParts[1]) ? $pathParts[1] : 'admin',
            'controller' => isset($pathParts[2]) ? $pathParts[2] : 'index',
            'action' => isset($pathParts[3]) ? $pathParts[3] : 'index'
        );
    }

    /**
     * Retrieve default path part by code
     *
     * @param string $code
     * @return string
     */
    public function getPart($code)
    {
        return isset($this->_parts[$code]) ? $this->_parts[$code] : null;
    }
}
