<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\ProductList;

use Magento\Catalog\Model\Session as CatalogSession;

/**
 * Class ToolbarMemorizer
 *
 * Responds for saving toolbar settings to catalog session
 */
class ToolbarMemorizer
{
    /**
     * @var CatalogSession
     */
    private $catalogSession;

    /**
     * @var Toolbar
     */
    private $toolbarModel;

    /**
     * @var bool
     */
    private $paramsMemorizeAllowed = true;

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
     * @param Toolbar $toolbarModel
     * @param CatalogSession $catalogSession
     */
    public function __construct(
        Toolbar $toolbarModel,
        CatalogSession $catalogSession
    ) {
        $this->toolbarModel = $toolbarModel;
        $this->catalogSession = $catalogSession;
    }

    /**
     * Get sort order
     *
     * @return string|bool
     */
    public function getOrder()
    {
        if ($this->order === null) {
            $this->order = $this->toolbarModel->getOrder() ??
                $this->catalogSession->getData(Toolbar::ORDER_PARAM_NAME);
        }
        return $this->order;
    }

    /**
     * Get sort direction
     *
     * @return string|bool
     */
    public function getDirection()
    {
        if ($this->direction === null) {
            $this->direction = $this->toolbarModel->getDirection() ??
                $this->catalogSession->getData(Toolbar::DIRECTION_PARAM_NAME);
        }
        return $this->direction;
    }

    /**
     * Get sort mode
     *
     * @return string|bool
     */
    public function getMode()
    {
        if ($this->mode === null) {
            $this->mode = $this->toolbarModel->getMode() ??
                $this->catalogSession->getData(Toolbar::MODE_PARAM_NAME);
        }
        return $this->mode;
    }

    /**
     * Get products per page limit
     *
     * @return string|bool
     */
    public function getLimit()
    {
        if ($this->limit === null) {
            $this->limit = $this->toolbarModel->getLimit() ??
                $this->catalogSession->getData(Toolbar::LIMIT_PARAM_NAME);
        }
        return $this->limit;
    }

    /**
     * Disable list state params memorizing
     *
     * @return $this
     */
    public function disableParamsMemorizing()
    {
        $this->paramsMemorizeAllowed = false;
        return $this;
    }

    /**
     * Method to save all catalog parameters in catalog session
     *
     * @return void
     */
    public function memorizeParams()
    {
        $this->memorizeParam(Toolbar::ORDER_PARAM_NAME, $this->getOrder())
            ->memorizeParam(Toolbar::DIRECTION_PARAM_NAME, $this->getDirection())
            ->memorizeParam(Toolbar::MODE_PARAM_NAME, $this->getMode())
            ->memorizeParam(Toolbar::LIMIT_PARAM_NAME, $this->getLimit());
    }

    /**
     * Memorize parameter value for session
     *
     * @param string $param parameter name
     * @param mixed $value parameter value
     * @return $this
     */
    private function memorizeParam($param, $value)
    {
        if ($value && $this->paramsMemorizeAllowed &&
            !$this->catalogSession->getParamsMemorizeDisabled() &&
            $this->catalogSession->getData($param) != $value
        ) {
            $this->catalogSession->setData($param, $value);
        }
        return $this;
    }
}
