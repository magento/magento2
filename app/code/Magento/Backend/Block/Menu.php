<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Backend\Block;

/**
 * Backend menu block
 *
 * @method \Magento\Backend\Block\Menu setAdditionalCacheKeyInfo(array $cacheKeyInfo)
 * @method array getAdditionalCacheKeyInfo()
 */
class Menu extends \Magento\Backend\Block\Template
{
    const CACHE_TAGS = 'BACKEND_MAINMENU';

    /**
     * @var string
     */
    protected $_containerRenderer;

    /**
     * @var string
     */
    protected $_itemRenderer;

    /**
     * Backend URL instance
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_url;

    /**
     * Current selected item
     *
     * @var \Magento\Backend\Model\Menu\Item|null|bool
     */
    protected $_activeItemModel = null;

    /**
     * @var \Magento\Backend\Model\Menu\Filter\IteratorFactory
     */
    protected $_iteratorFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Backend\Model\Menu\Config
     */
    protected $_menuConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var MenuItemChecker
     */
    private $menuItemChecker;

    /**
     * @var AnchorRenderer
     */
    private $anchorRenderer;

    /**
     * @param Template\Context $context
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Backend\Model\Menu\Filter\IteratorFactory $iteratorFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Backend\Model\Menu\Config $menuConfig
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     * @param MenuItemChecker|null $menuItemChecker
     * @param AnchorRenderer|null $anchorRenderer
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Backend\Model\Menu\Filter\IteratorFactory $iteratorFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Backend\Model\Menu\Config $menuConfig,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = [],
        MenuItemChecker $menuItemChecker = null,
        AnchorRenderer $anchorRenderer = null
    ) {
        $this->_url = $url;
        $this->_iteratorFactory = $iteratorFactory;
        $this->_authSession = $authSession;
        $this->_menuConfig = $menuConfig;
        $this->_localeResolver = $localeResolver;
        $this->menuItemChecker =  $menuItemChecker;
        $this->anchorRenderer = $anchorRenderer;
        parent::__construct($context, $data);
    }

    /**
     * Initialize template and cache settings
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCacheTags([self::CACHE_TAGS]);
    }

    /**
     * Render menu item anchor label
     *
     * @param \Magento\Backend\Model\Menu\Item $menuItem
     * @return string
     */
    protected function _getAnchorLabel($menuItem)
    {
        return $this->escapeHtml(__($menuItem->getTitle()));
    }

    /**
     * Render menu item mouse events
     * @param \Magento\Backend\Model\Menu\Item $menuItem
     * @return string
     */
    protected function _renderMouseEvent($menuItem)
    {
        return $menuItem->hasChildren() ? 'onmouseover="Element.addClassName(this,\'over\')" onmouseout="Element.removeClassName(this,\'over\')"' : '';
    }

    /**
     * Render item css class
     *
     * @param \Magento\Backend\Model\Menu\Item $menuItem
     * @param int $level
     * @return string
     */
    protected function _renderItemCssClass($menuItem, $level)
    {
        $isLast = 0 == $level && (bool)$this->getMenuModel()->isLast($menuItem) ? 'last' : '';
        $output = ($this->menuItemChecker->isItemActive(
            $this->getActiveItemModel(),
                $menuItem,
                $level
            ) ? '_current _active' : '') .
            ' ' .
            ($menuItem->hasChildren() ? 'parent' : '') .
            ' ' .
            $isLast .
            ' ' .
            'level-' .
            $level;
        return $output;
    }

    /**
     * Get menu filter iterator
     *
     * @param \Magento\Backend\Model\Menu $menu
     * @return \Magento\Backend\Model\Menu\Filter\Iterator
     */
    protected function _getMenuIterator($menu)
    {
        return $this->_iteratorFactory->create(['iterator' => $menu->getIterator()]);
    }

    /**
     * Processing block html after rendering
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        $html = preg_replace_callback(
            '#' . \Magento\Backend\Model\UrlInterface::SECRET_KEY_PARAM_NAME . '/\$([^\/].*)/([^\/].*)/([^\$].*)\$#U',
            [$this, '_callbackSecretKey'],
            $html
        );

        return $html;
    }

    /**
     * Replace Callback Secret Key
     *
     * @param string[] $match
     * @return string
     */
    protected function _callbackSecretKey($match)
    {
        return \Magento\Backend\Model\UrlInterface::SECRET_KEY_PARAM_NAME . '/' . $this->_url->getSecretKey(
            $match[1],
            $match[2],
            $match[3]
        );
    }

    /**
     * Retrieve cache lifetime
     *
     * @return int
     */
    public function getCacheLifetime()
    {
        return 86400;
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = [
            'admin_top_nav',
            $this->getActive(),
            $this->_authSession->getUser()->getId(),
            $this->_localeResolver->getLocale(),
        ];
        // Add additional key parameters if needed
        $newCacheKeyInfo = $this->getAdditionalCacheKeyInfo();
        if (is_array($newCacheKeyInfo) && !empty($newCacheKeyInfo)) {
            $cacheKeyInfo = array_merge($cacheKeyInfo, $newCacheKeyInfo);
        }
        return $cacheKeyInfo;
    }

    /**
     * Get menu config model
     *
     * @return \Magento\Backend\Model\Menu
     */
    public function getMenuModel()
    {
        return $this->_menuConfig->getMenu();
    }

    /**
     * Render menu
     *
     * @param \Magento\Backend\Model\Menu $menu
     * @param int $level
     * @return string HTML
     */
    public function renderMenu($menu, $level = 0)
    {
        $output = '<ul ' . (0 == $level ? 'id="nav" role="menubar"' : '') . ' >';

        /** @var $menuItem \Magento\Backend\Model\Menu\Item  */
        foreach ($this->_getMenuIterator($menu) as $menuItem) {
            $output .= '<li ' . $this->_renderMouseEvent(
                $menuItem
            ) . ' class="' . $this->_renderItemCssClass(
                $menuItem,
                $level
            ) . '"' . $this->getUiId(
                $menuItem->getId()
            ) . 'role="menuitem">';

            $output .= $this->anchorRenderer->renderAnchor($this->getActiveItemModel(), $menuItem, $level);

            if ($menuItem->hasChildren()) {
                $output .= $this->renderMenu($menuItem->getChildren(), $level + 1);
            }
            $output .= '</li>';
        }
        $output .= '</ul>';

        return $output;
    }

    /**
     * Count All Subnavigation Items
     *
     * @param \Magento\Backend\Model\Menu $items
     * @return int
     */
    protected function _countItems($items)
    {
        $total = count($items);
        foreach ($items as $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            if ($item->hasChildren()) {
                $total += $this->_countItems($item->getChildren());
            }
        }
        return $total;
    }

    /**
     * Building Array with Column Brake Stops
     *
     * @param \Magento\Backend\Model\Menu $items
     * @param int $limit
     * @return array|void
     * @todo: Add Depth Level limit, and better logic for columns
     */
    protected function _columnBrake($items, $limit)
    {
        $total = $this->_countItems($items);
        if ($total <= $limit) {
            return;
        }
        $result[] = ['total' => $total, 'max' => ceil($total / ceil($total / $limit))];
        $count = 0;
        foreach ($items as $item) {
            $place = $this->_countItems($item->getChildren()) + 1;
            $count += $place;
            if ($place - $result[0]['max'] > $limit - $result[0]['max']) {
                $colbrake = true;
                $count = 0;
            } elseif ($count - $result[0]['max'] > $limit - $result[0]['max']) {
                $colbrake = true;
                $count = $place;
            } else {
                $colbrake = false;
            }
            $result[] = ['place' => $place, 'colbrake' => $colbrake];
        }
        return $result;
    }

    /**
     * Add sub menu HTML code for current menu item
     *
     * @param \Magento\Backend\Model\Menu\Item $menuItem
     * @param int $level
     * @param int $limit
     * @param $id int
     * @return string HTML code
     */
    protected function _addSubMenu($menuItem, $level, $limit, $id = null)
    {
        $output = '';
        if (!$menuItem->hasChildren()) {
            return $output;
        }
        $output .= '<div class="submenu"' . ($level == 0 && isset($id) ? ' aria-labelledby="' . $id . '"' : '') . '>';
        $colStops = null;
        if ($level == 0 && $limit) {
            $colStops = $this->_columnBrake($menuItem->getChildren(), $limit);
            $output .= '<strong class="submenu-title">' . $this->_getAnchorLabel($menuItem) . '</strong>';
            $output .= '<a href="#" class="action-close _close" data-role="close-submenu"></a>';
        }

        $output .= $this->renderNavigation($menuItem->getChildren(), $level + 1, $limit, $colStops);
        $output .= '</div>';
        return $output;
    }

    /**
     * Render Navigation
     *
     * @param \Magento\Backend\Model\Menu $menu
     * @param int $level
     * @param int $limit
     * @param array $colBrakes
     * @return string HTML
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function renderNavigation($menu, $level = 0, $limit = 0, $colBrakes = [])
    {
        $itemPosition = 1;
        $outputStart = '<ul ' . (0 == $level ? 'id="nav" role="menubar"' : 'role="menu"') . ' >';
        $output = '';

        /** @var $menuItem \Magento\Backend\Model\Menu\Item  */
        foreach ($this->_getMenuIterator($menu) as $menuItem) {
            $menuId = $menuItem->getId();
            $itemName = substr($menuId, strrpos($menuId, '::') + 2);
            $itemClass = str_replace('_', '-', strtolower($itemName));

            if (count($colBrakes) && $colBrakes[$itemPosition]['colbrake']) {
                $output .= '</ul></li><li class="column"><ul role="menu">';
            }

            $id = $this->getJsId($menuItem->getId());
            $subMenu = $this->_addSubMenu($menuItem, $level, $limit, $id);
            $anchor = $this->anchorRenderer->renderAnchor($this->getActiveItemModel(), $menuItem, $level);
            $output .= '<li ' . $this->getUiId($menuItem->getId())
                . ' class="item-' . $itemClass . ' ' . $this->_renderItemCssClass($menuItem, $level)
                . ($level == 0 ? '" id="' . $id . '" aria-haspopup="true' : '')
                . '" role="menu-item">' . $anchor . $subMenu . '</li>';
            $itemPosition++;
        }

        if (count($colBrakes) && $limit) {
            $output = '<li class="column"><ul role="menu">' . $output . '</ul></li>';
        }

        return $outputStart . $output . '</ul>';
    }

    /**
     * Get current selected menu item
     *
     * @return \Magento\Backend\Model\Menu\Item|null
     */
    public function getActiveItemModel()
    {
        if (is_null($this->_activeItemModel)) {
            $this->_activeItemModel = $this->getMenuModel()->get($this->getActive());
            if (false == $this->_activeItemModel instanceof \Magento\Backend\Model\Menu\Item) {
                $this->_activeItemModel = null;
            }
        }
        return $this->_activeItemModel;
    }
}
