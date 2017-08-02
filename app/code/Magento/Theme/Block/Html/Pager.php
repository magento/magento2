<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Html;

/**
 * Html pager block
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @api
 * @since 2.0.0
 */
class Pager extends \Magento\Framework\View\Element\Template
{
    /**
     * Current template name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Theme::html/pager.phtml';

    /**
     * @var \Magento\Framework\Data\Collection
     * @since 2.0.0
     */
    protected $_collection;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_pageVarName = 'p';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_limitVarName = 'limit';

    /**
     * The list of available pager limits
     *
     * @var array
     * @since 2.0.0
     */
    protected $_availableLimit = [10 => 10, 20 => 20, 50 => 50];

    /**
     * @var int
     * @since 2.0.0
     */
    protected $_displayPages = 5;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $_showPerPage = true;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $_limit;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $_outputRequired = true;

    /**
     * Pages quantity per frame
     *
     * @var int
     * @since 2.0.0
     */
    protected $_frameLength = 5;

    /**
     * Next/previous page position relatively to the current frame
     *
     * @var int
     * @since 2.0.0
     */
    protected $_jump = 5;

    /**
     * Frame initialization flag
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_frameInitialized = false;

    /**
     * Start page position in frame
     *
     * @var int
     * @since 2.0.0
     */
    protected $_frameStart;

    /**
     * Finish page position in frame
     *
     * @var int
     * @since 2.0.0
     */
    protected $_frameEnd;

    /**
     * Url Fragment for pagination
     *
     * @var string|null
     * @since 2.0.0
     */
    protected $_fragment = null;

    /**
     * Set pager data
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setData('show_amounts', true);
        $this->setData('use_container', true);
    }

    /**
     * Return current page
     *
     * @return int
     * @since 2.0.0
     */
    public function getCurrentPage()
    {
        if (is_object($this->_collection)) {
            return $this->_collection->getCurPage();
        }
        return (int)$this->getRequest()->getParam($this->getPageVarName(), 1);
    }

    /**
     * Return current page limit
     *
     * @return int
     * @since 2.0.0
     */
    public function getLimit()
    {
        if ($this->_limit !== null) {
            return $this->_limit;
        }

        $limits = $this->getAvailableLimit();
        if ($limit = $this->getRequest()->getParam($this->getLimitVarName())) {
            if (isset($limits[$limit])) {
                return $limit;
            }
        }

        $limits = array_keys($limits);
        return $limits[0];
    }

    /**
     * Setter for limit items per page
     *
     * @param int $limit
     * @return $this
     * @since 2.0.0
     */
    public function setLimit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * Set collection for pagination
     *
     * @param  \Magento\Framework\Data\Collection $collection
     * @return $this
     * @since 2.0.0
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection->setCurPage($this->getCurrentPage());
        // If not int - then not limit
        if ((int)$this->getLimit()) {
            $this->_collection->setPageSize($this->getLimit());
        }

        $this->_setFrameInitialized(false);

        return $this;
    }

    /**
     * @return \Magento\Framework\Data\Collection
     * @since 2.0.0
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * @param string $varName
     * @return $this
     * @since 2.0.0
     */
    public function setPageVarName($varName)
    {
        $this->_pageVarName = $varName;
        return $this;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getPageVarName()
    {
        return $this->_pageVarName;
    }

    /**
     * @param bool $varName
     * @return $this
     * @since 2.0.0
     */
    public function setShowPerPage($varName)
    {
        $this->_showPerPage = $varName;
        return $this;
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isShowPerPage()
    {
        if (sizeof($this->getAvailableLimit()) <= 1) {
            return false;
        }
        return $this->_showPerPage;
    }

    /**
     * Set the name for pager limit data
     *
     * @param string $varName
     * @return $this
     * @since 2.0.0
     */
    public function setLimitVarName($varName)
    {
        $this->_limitVarName = $varName;
        return $this;
    }

    /**
     * Retrieve name for pager limit data
     *
     * @return string
     * @since 2.0.0
     */
    public function getLimitVarName()
    {
        return $this->_limitVarName;
    }

    /**
     * Set pager limit
     *
     * @param array $limits
     * @return $this
     * @since 2.0.0
     */
    public function setAvailableLimit(array $limits)
    {
        $this->_availableLimit = $limits;
        return $this;
    }

    /**
     * Retrieve pager limit
     *
     * @return array
     * @since 2.0.0
     */
    public function getAvailableLimit()
    {
        return $this->_availableLimit;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getFirstNum()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize() * ($collection->getCurPage() - 1) + 1;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getLastNum()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize() * ($collection->getCurPage() - 1) + $collection->count();
    }

    /**
     * Retrieve total number of pages
     *
     * @return int
     * @since 2.0.0
     */
    public function getTotalNum()
    {
        return $this->getCollection()->getSize();
    }

    /**
     * Check if current page is a first page in collection
     *
     * @return bool
     * @since 2.0.0
     */
    public function isFirstPage()
    {
        return $this->getCollection()->getCurPage() == 1;
    }

    /**
     * Retrieve number of last page
     *
     * @return int
     * @since 2.0.0
     */
    public function getLastPageNum()
    {
        return $this->getCollection()->getLastPageNumber();
    }

    /**
     * Check if current page is a last page in collection
     *
     * @return bool
     * @since 2.0.0
     */
    public function isLastPage()
    {
        return $this->getCollection()->getCurPage() >= $this->getLastPageNum();
    }

    /**
     * @param int $limit
     * @return bool
     * @since 2.0.0
     */
    public function isLimitCurrent($limit)
    {
        return $limit == $this->getLimit();
    }

    /**
     * @param int $page
     * @return bool
     * @since 2.0.0
     */
    public function isPageCurrent($page)
    {
        return $page == $this->getCurrentPage();
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getPages()
    {
        $collection = $this->getCollection();
        if ($collection->getLastPageNumber() <= $this->_displayPages) {
            return range(1, $collection->getLastPageNumber());
        } else {
            $half = ceil($this->_displayPages / 2);
            if ($collection->getCurPage() >= $half &&
                $collection->getCurPage() <= $collection->getLastPageNumber() - $half
            ) {
                $start = $collection->getCurPage() - $half + 1;
                $finish = $start + $this->_displayPages - 1;
            } elseif ($collection->getCurPage() < $half) {
                $start = 1;
                $finish = $this->_displayPages;
            } elseif ($collection->getCurPage() > $collection->getLastPageNumber() - $half) {
                $finish = $collection->getLastPageNumber();
                $start = $finish - $this->_displayPages + 1;
            }
            return range($start, $finish);
        }
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getFirstPageUrl()
    {
        return $this->getPageUrl(1);
    }

    /**
     * Retrieve previous page URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getPreviousPageUrl()
    {
        return $this->getPageUrl($this->getCollection()->getCurPage(-1));
    }

    /**
     * Retrieve next page URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getNextPageUrl()
    {
        return $this->getPageUrl($this->getCollection()->getCurPage(+1));
    }

    /**
     * Retrieve last page URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getLastPageUrl()
    {
        return $this->getPageUrl($this->getCollection()->getLastPageNumber());
    }

    /**
     * Retrieve page URL
     *
     * @param string $page
     * @return string
     * @since 2.0.0
     */
    public function getPageUrl($page)
    {
        return $this->getPagerUrl([$this->getPageVarName() => $page]);
    }

    /**
     * @param int $limit
     * @return string
     * @since 2.0.0
     */
    public function getLimitUrl($limit)
    {
        return $this->getPagerUrl([$this->getLimitVarName() => $limit]);
    }

    /**
     * Retrieve page URL by defined parameters
     *
     * @param array $params
     * @return string
     * @since 2.0.0
     */
    public function getPagerUrl($params = [])
    {
        $urlParams = [];
        $urlParams['_current'] = true;
        $urlParams['_escape'] = true;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_fragment'] = $this->getFragment();
        $urlParams['_query'] = $params;

        return $this->getUrl($this->getPath(), $urlParams);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function getPath()
    {
        return $this->_getData('path') ?: '*/*/*';
    }

    /**
     * Getter for $_frameStart
     *
     * @return int
     * @since 2.0.0
     */
    public function getFrameStart()
    {
        $this->_initFrame();
        return $this->_frameStart;
    }

    /**
     * Getter for $_frameEnd
     *
     * @return int
     * @since 2.0.0
     */
    public function getFrameEnd()
    {
        $this->_initFrame();
        return $this->_frameEnd;
    }

    /**
     * Return array of pages in frame
     *
     * @return array
     * @since 2.0.0
     */
    public function getFramePages()
    {
        $start = $this->getFrameStart();
        $end = $this->getFrameEnd();
        return range($start, $end);
    }

    /**
     * Return page number of Previous jump
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getPreviousJumpPage()
    {
        if (!$this->getJump()) {
            return null;
        }

        $frameStart = $this->getFrameStart();
        if ($frameStart - 1 > 1) {
            return max(2, $frameStart - $this->getJump());
        }

        return null;
    }

    /**
     * Prepare URL for Previous Jump
     *
     * @return string
     * @since 2.0.0
     */
    public function getPreviousJumpUrl()
    {
        return $this->getPageUrl($this->getPreviousJumpPage());
    }

    /**
     * Return page number of Next jump
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getNextJumpPage()
    {
        if (!$this->getJump()) {
            return null;
        }

        $frameEnd = $this->getFrameEnd();
        if ($this->getLastPageNum() - $frameEnd > 1) {
            return min($this->getLastPageNum() - 1, $frameEnd + $this->getJump());
        }

        return null;
    }

    /**
     * Prepare URL for Next Jump
     *
     * @return string
     * @since 2.0.0
     */
    public function getNextJumpUrl()
    {
        return $this->getPageUrl($this->getNextJumpPage());
    }

    /**
     * Getter for $_frameLength
     *
     * @return int
     * @since 2.0.0
     */
    public function getFrameLength()
    {
        return $this->_frameLength;
    }

    /**
     * Getter for $_jump
     *
     * @return int
     * @since 2.0.0
     */
    public function getJump()
    {
        return $this->_jump;
    }

    /**
     * Setter for $_frameLength
     *
     * @param int $frame
     * @return $this
     * @since 2.0.0
     */
    public function setFrameLength($frame)
    {
        $frame = abs(intval($frame));
        if ($frame == 0) {
            $frame = $this->_frameLength;
        }
        if ($this->getFrameLength() != $frame) {
            $this->_setFrameInitialized(false);
            $this->_frameLength = $frame;
        }

        return $this;
    }

    /**
     * Setter for $_jump
     *
     * @param int $jump
     * @return $this
     * @since 2.0.0
     */
    public function setJump($jump)
    {
        $jump = abs(intval($jump));
        if ($this->getJump() != $jump) {
            $this->_setFrameInitialized(false);
            $this->_jump = $jump;
        }

        return $this;
    }

    /**
     * Whether to show first page in pagination or not
     *
     * @return bool
     * @since 2.0.0
     */
    public function canShowFirst()
    {
        return $this->getJump() > 1 && $this->getFrameStart() > 1;
    }

    /**
     * Whether to show last page in pagination or not
     *
     * @return bool
     * @since 2.0.0
     */
    public function canShowLast()
    {
        return $this->getJump() > 1 && $this->getFrameEnd() < $this->getLastPageNum();
    }

    /**
     * Whether to show link to Previous Jump
     *
     * @return bool
     * @since 2.0.0
     */
    public function canShowPreviousJump()
    {
        return $this->getPreviousJumpPage() !== null;
    }

    /**
     * Whether to show link to Next Jump
     *
     * @return bool
     * @since 2.0.0
     */
    public function canShowNextJump()
    {
        return $this->getNextJumpPage() !== null;
    }

    /**
     * Initialize frame data, such as frame start, frame start etc.
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initFrame()
    {
        if (!$this->isFrameInitialized()) {
            $start = 0;
            $end = 0;

            $collection = $this->getCollection();
            if ($collection->getLastPageNumber() <= $this->getFrameLength()) {
                $start = 1;
                $end = $collection->getLastPageNumber();
            } else {
                $half = ceil($this->getFrameLength() / 2);
                if ($collection->getCurPage() >= $half &&
                    $collection->getCurPage() <= $collection->getLastPageNumber() - $half
                ) {
                    $start = $collection->getCurPage() - $half + 1;
                    $end = $start + $this->getFrameLength() - 1;
                } elseif ($collection->getCurPage() < $half) {
                    $start = 1;
                    $end = $this->getFrameLength();
                } elseif ($collection->getCurPage() > $collection->getLastPageNumber() - $half) {
                    $end = $collection->getLastPageNumber();
                    $start = $end - $this->getFrameLength() + 1;
                }
            }
            $this->_frameStart = $start;
            $this->_frameEnd = $end;

            $this->_setFrameInitialized(true);
        }

        return $this;
    }

    /**
     * Setter for flag _frameInitialized
     *
     * @param bool $flag
     * @return $this
     * @since 2.0.0
     */
    protected function _setFrameInitialized($flag)
    {
        $this->_frameInitialized = (bool)$flag;
        return $this;
    }

    /**
     * Check if frame data was initialized
     *
     * @return bool
     * @since 2.0.0
     */
    public function isFrameInitialized()
    {
        return $this->_frameInitialized;
    }

    /**
     * Getter for alternative text for Previous link in pagination frame
     *
     * @return string
     * @since 2.0.0
     */
    public function getAnchorTextForPrevious()
    {
        return $this->_scopeConfig->getValue(
            'design/pagination/anchor_text_for_previous',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Getter for alternative text for Next link in pagination frame
     *
     * @return string
     * @since 2.0.0
     */
    public function getAnchorTextForNext()
    {
        return $this->_scopeConfig->getValue(
            'design/pagination/anchor_text_for_next',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Set whether output of the pager is mandatory
     *
     * @param bool $isRequired
     * @return $this
     * @since 2.0.0
     */
    public function setIsOutputRequired($isRequired)
    {
        $this->_outputRequired = (bool)$isRequired;
        return $this;
    }

    /**
     * Determine whether the pagination should be eventually rendered
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if ($this->_outputRequired || $this->getTotalNum() > $this->getLimit()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Get the URL fragment
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getFragment()
    {
        return $this->_fragment;
    }

    /**
     * Set the URL fragment
     *
     * @param string|null $fragment
     * @return $this
     * @since 2.0.0
     */
    public function setFragment($fragment)
    {
        $this->_fragment = $fragment;
        return $this;
    }
}
