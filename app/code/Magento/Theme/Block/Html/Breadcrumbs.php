<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Theme\Block\Html;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;

/**
 * Html page breadcrumbs block
 *
 * @api
 * @since 100.0.2
 */
class Breadcrumbs extends Template
{
    /**
     * Current template name
     *
     * @var string
     */
    protected $_template = 'Magento_Theme::html/breadcrumbs.phtml';

    /**
     * List of available breadcrumb properties
     *
     * @var string[]
     */
    protected $_properties = ['label', 'title', 'link', 'first', 'last', 'readonly'];

    /**
     * List of breadcrumbs
     *
     * @var array
     */
    protected $_crumbs = [];

    /**
     * Cache key info
     *
     * @var null|array
     */
    protected $_cacheKeyInfo;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Template\Context $context
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        Template\Context $context,
        array $data = [],
        Json $serializer = null
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Add crumb
     *
     * @param string $crumbName Name of the crumb
     * @param array $crumbInfo Crumb data
     * @return $this
     */
    public function addCrumb(string $crumbName, array $crumbInfo): self
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
     * Add crumb after another
     *
     * @param string $crumbName Name of the crumb
     * @param array $crumbInfo Crumb data
     * @param string $after Name of the crumb to insert after
     * @return $this
     */
    public function addCrumbAfter(string $crumbName, array $crumbInfo, string $after): self
    {
        foreach ($this->_properties as $key) {
            if (!isset($crumbInfo[$key])) {
                $crumbInfo[$key] = null;
            }
        }

        if ((!isset($this->_crumbs[$crumbName])) || (!$this->_crumbs[$crumbName]['readonly'])) {
            if (!isset($this->_crumbs[$after])) {
                $this->addCrumb($crumbName, $crumbInfo);
                return $this;
            }

            $offset = array_search($after, array_keys($this->_crumbs)) + 1;
            $crumbsBefore = array_slice($this->_crumbs, 0, $offset, true);
            $crumbsAfter = array_slice($this->_crumbs, $offset, null, true);
            $this->_crumbs = $crumbsBefore + [$crumbName => $crumbInfo] + $crumbsAfter;
        }

        return $this;
    }

    /**
     * Add crumb before another
     *
     * @param string $crumbName Name of the crumb
     * @param array $crumbInfo Crumb data
     * @param string $before ame of the crumb to insert before
     * @return $this
     */
    public function addCrumbBefore(string $crumbName, array $crumbInfo, string $before): self
    {
        if (!isset($this->_crumbs[$before])) {
            $this->addCrumb($crumbName, $crumbInfo);
            return $this;
        }

        $keys = array_keys($this->_crumbs);
        $offset = array_search($before, $keys);

        if ($offset) {
            $this->addCrumbAfter($crumbName, $crumbInfo, $keys[$offset - 1]);
            return $this;
        }

        foreach ($this->_properties as $key) {
            if (!isset($crumbInfo[$key])) {
                $crumbInfo[$key] = null;
            }
        }

        $this->_crumbs = [$crumbName => $crumbInfo] + $this->_crumbs;

        return $this;
    }

    /**
     * Remove crumb
     *
     * @param string $crumbName Name of the crumb
     * @return $this
     */
    public function removeCrumb(string $crumbName): self
    {
        if (isset($this->_crumbs[$crumbName])) {
            unset($this->_crumbs[$crumbName]);
        }
        return $this;
    }

    /**
     * Get all crumbs

     * @return array
     */
    public function getCrumbs(): array
    {
        return $this->_crumbs;
    }

    /**
     * Get cache key informative items
     *
     * Provide string array key to share specific info item with FPC placeholder
     *
     * @return array
     */
    public function getCacheKeyInfo(): array
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
     */
    protected function _toHtml(): string
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
