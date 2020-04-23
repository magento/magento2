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
 * Reviews admin controller.
 */
abstract class Product extends Action
{
    /**
     * Authorization resource
     */
    public const ADMIN_RESOURCE = 'Magento_Review::reviews_all';

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = ['edit'];

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Review model factory
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * Rating model factory
     *
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $ratingFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
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
}
