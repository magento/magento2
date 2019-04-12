<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\ProductList;

use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Responds for saving toolbar settings to catalog session.
 */
class ToolbarMemorizer
{
    /**
     * XML PATH to enable/disable saving toolbar parameters to session
     */
    const XML_PATH_CATALOG_REMEMBER_PAGINATION = 'catalog/frontend/remember_pagination';

    /**
     * @var CatalogSession
     */
    private $catalogSession;

    /**
     * @var Toolbar
     */
    private $toolbarModel;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string|bool
     */
    private $order;

    /**
     * @var string|bool
     */
    private $direction;

    /**
     * @var string|bool
     */
    private $mode;

    /**
     * @var string|bool
     */
    private $limit;

    /**
     * @var bool
     */
    private $isMemorizingAllowed;

    /**
     * @param Toolbar $toolbarModel
     * @param CatalogSession $catalogSession
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Toolbar $toolbarModel,
        CatalogSession $catalogSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->toolbarModel = $toolbarModel;
        $this->catalogSession = $catalogSession;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get sort order.
     *
     * @return string|bool|null
     */
    public function getOrder()
    {
        if ($this->order === null) {
            $this->order = $this->toolbarModel->getOrder() ??
                ($this->isMemorizingAllowed() ? $this->catalogSession->getData(Toolbar::ORDER_PARAM_NAME) : null);
        }

        return $this->order;
    }

    /**
     * Get sort direction.
     *
     * @return string|bool|null
     */
    public function getDirection()
    {
        if ($this->direction === null) {
            $this->direction = $this->toolbarModel->getDirection() ??
                ($this->isMemorizingAllowed() ? $this->catalogSession->getData(Toolbar::DIRECTION_PARAM_NAME) : null);
        }

        return $this->direction;
    }

    /**
     * Get sort mode.
     *
     * @return string|bool|null
     */
    public function getMode()
    {
        if ($this->mode === null) {
            $this->mode = $this->toolbarModel->getMode() ??
                ($this->isMemorizingAllowed() ? $this->catalogSession->getData(Toolbar::MODE_PARAM_NAME) : null);
        }

        return $this->mode;
    }

    /**
     * Get products per page limit.
     *
     * @return string|bool|null
     */
    public function getLimit()
    {
        if ($this->limit === null) {
            $this->limit = $this->toolbarModel->getLimit() ??
                ($this->isMemorizingAllowed() ? $this->catalogSession->getData(Toolbar::LIMIT_PARAM_NAME) : null);
        }

        return $this->limit;
    }

    /**
     * Method to save all catalog parameters in catalog session.
     *
     * @return void
     */
    public function memorizeParams()
    {
        if (!$this->catalogSession->getParamsMemorizeDisabled() && $this->isMemorizingAllowed()) {
            $this->memorizeParam(Toolbar::ORDER_PARAM_NAME, $this->getOrder())
                ->memorizeParam(Toolbar::DIRECTION_PARAM_NAME, $this->getDirection())
                ->memorizeParam(Toolbar::MODE_PARAM_NAME, $this->getMode())
                ->memorizeParam(Toolbar::LIMIT_PARAM_NAME, $this->getLimit());
        }
    }

    /**
     * Check configuration for enabled/disabled toolbar memorizing.
     *
     * @return bool
     */
    public function isMemorizingAllowed(): bool
    {
        if ($this->isMemorizingAllowed === null) {
            $this->isMemorizingAllowed = $this->scopeConfig->isSetFlag(self::XML_PATH_CATALOG_REMEMBER_PAGINATION);
        }

        return $this->isMemorizingAllowed;
    }

    /**
     * Memorize parameter value for session.
     *
     * @param string $param parameter name
     * @param mixed $value parameter value
     * @return ToolbarMemorizer
     */
    private function memorizeParam(string $param, $value): ToolbarMemorizer
    {
        if ($value && $this->catalogSession->getData($param) != $value) {
            $this->catalogSession->setData($param, $value);
        }

        return $this;
    }
}
