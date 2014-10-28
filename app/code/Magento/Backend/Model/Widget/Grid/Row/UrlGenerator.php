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
namespace Magento\Backend\Model\Widget\Grid\Row;

/**
 * Grid row url generator
 */
class UrlGenerator implements \Magento\Backend\Model\Widget\Grid\Row\GeneratorInterface
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_urlModel;

    /**
     * @var string
     */
    protected $_path;

    /**
     * @var array
     */
    protected $_params = array();

    /**
     * @var array
     */
    protected $_extraParamsTemplate = array();

    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param array $args
     * @throws \InvalidArgumentException
     */
    public function __construct(\Magento\Backend\Model\UrlInterface $backendUrl, array $args = array())
    {
        if (!isset($args['path'])) {
            throw new \InvalidArgumentException('Not all required parameters passed');
        }
        $this->_urlModel = isset($args['urlModel']) ? $args['urlModel'] : $backendUrl;
        $this->_path = (string)$args['path'];
        if (isset($args['params'])) {
            $this->_params = (array)$args['params'];
        }
        if (isset($args['extraParamsTemplate'])) {
            $this->_extraParamsTemplate = (array)$args['extraParamsTemplate'];
        }
    }

    /**
     * Create url for passed item using passed url model
     *
     * @param \Magento\Framework\Object $item
     * @return string
     */
    public function getUrl($item)
    {
        if (!empty($this->_path)) {
            $params = $this->_prepareParameters($item);
            return $this->_urlModel->getUrl($this->_path, $params);
        }
        return '';
    }

    /**
     * Convert template params array and merge with preselected params
     *
     * @param \Magento\Framework\Object $item
     * @return array
     */
    protected function _prepareParameters($item)
    {
        $params = array();
        foreach ($this->_extraParamsTemplate as $paramKey => $paramValueMethod) {
            $params[$paramKey] = $item->{$paramValueMethod}();
        }
        return array_merge($this->_params, $params);
    }
}
