<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Html;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;

/**
 * Html page breadcrumbs block
 *
 * @api
 * @since 2.0.0
 */
class Breadcrumbs extends \Magento\Framework\View\Element\Template
{
    /**
     * Current template name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'html/breadcrumbs.phtml';

    /**
     * List of available breadcrumb properties
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_properties = ['label', 'title', 'link', 'first', 'last', 'readonly'];

    /**
     * List of breadcrumbs
     *
     * @var array
     * @since 2.0.0
     */
    protected $_crumbs;

    /**
     * Cache key info
     *
     * @var null|array
     * @since 2.0.0
     */
    protected $_cacheKeyInfo;

    /**
     * @var Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param Template\Context $context
     * @param array $data
     * @param Json|null $serializer
     * @since 2.2.0
     */
    public function __construct(
        Template\Context $context,
        array $data = [],
        Json $serializer = null
    ) {
        parent::__construct($context, $data);
        $this->serializer =
            $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Add crumb
     *
     * @param string $crumbName
     * @param array $crumbInfo
     * @return $this
     * @since 2.0.0
     */
    public function addCrumb($crumbName, $crumbInfo)
    {
        foreach ($this->_properties as $key) {
            if (!isset($crumbInfo[$key])) {
                $crumbInfo[$key] = null;
            }
        }

        if (!isset($this->_crumbs[$crumbName]) || !$this->_crumbs[$crumbName]['readonly']) {
            $this->_crumbs[$crumbName] = $crumbInfo;
        }

        return $this;
    }

    /**
     * Get cache key informative items
     *
     * Provide string array key to share specific info item with FPC placeholder
     *
     * @return array
     * @since 2.0.0
     */
    public function getCacheKeyInfo()
    {
        if ($this->_cacheKeyInfo === null) {
            $this->_cacheKeyInfo = parent::getCacheKeyInfo() + [
                'crumbs' => base64_encode($this->serializer->serialize($this->_crumbs)),
                'name' => $this->getNameInLayout()
            ];
        }
        return $this->_cacheKeyInfo;
    }

    /**
     * Render block HTML
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if (is_array($this->_crumbs)) {
            reset($this->_crumbs);
            $this->_crumbs[key($this->_crumbs)]['first'] = true;
            end($this->_crumbs);
            $this->_crumbs[key($this->_crumbs)]['last'] = true;
        }
        $this->assign('crumbs', $this->_crumbs);

        return parent::_toHtml();
    }
}
