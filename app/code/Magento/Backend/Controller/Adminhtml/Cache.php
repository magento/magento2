<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\State;
use Magento\Framework\App\ObjectManager;

abstract class Cache extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Backend::cache';

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_cacheFrontendPool;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var State
     */
    protected $state;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheState = $cacheState;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Check whether specified cache types exist
     *
     * @param array $types
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _validateTypes(array $types)
    {
        if (empty($types)) {
            return;
        }
        $allTypes = array_keys($this->_cacheTypeList->getTypes());
        $invalidTypes = array_diff($types, $allTypes);
        if (count($invalidTypes) > 0) {
            throw new LocalizedException(__('Specified cache type(s) don\'t exist: %1', join(', ', $invalidTypes)));
        }
    }

    /**
     * Check that production mode is enabled
     *
     * @return bool
     */
    protected function isProduction()
    {
        return $this->getState()->getMode() === State::MODE_PRODUCTION;
    }

    /**
     * Get State Instance
     *
     * @return State
     * @deprecated
     */
    private function getState()
    {
        if ($this->state === null) {
            $this->state = ObjectManager::getInstance()->get(State::class);
        }

        return $this->state;
    }
}
