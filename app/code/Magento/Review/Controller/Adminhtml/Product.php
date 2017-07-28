<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\RatingFactory;

/**
 * Reviews admin controller
 * @since 2.0.0
 */
abstract class Product extends Action
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     * @since 2.0.0
     */
    protected $_publicActions = ['edit'];

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $coreRegistry = null;

    /**
     * Review model factory
     *
     * @var \Magento\Review\Model\ReviewFactory
     * @since 2.0.0
     */
    protected $reviewFactory;

    /**
     * Rating model factory
     *
     * @var \Magento\Review\Model\RatingFactory
     * @since 2.0.0
     */
    protected $ratingFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ReviewFactory $reviewFactory,
        RatingFactory $ratingFactory
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->reviewFactory = $reviewFactory;
        $this->ratingFactory = $ratingFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'pending':
                return $this->_authorization->isAllowed('Magento_Review::pending');
                break;
            default:
                return $this->_authorization->isAllowed('Magento_Review::reviews_all');
                break;
        }
    }
}
