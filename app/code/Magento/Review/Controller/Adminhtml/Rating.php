<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

/**
 * Admin ratings controller
 */
abstract class Rating extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Review::ratings';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * @deprecated Misspelled method
     * @see initEntityId
     */
    protected function initEnityId()
    {
        $this->initEntityId();
    }

    /**
     * @return void
     */
    protected function initEntityId()
    {
        $this->coreRegistry->register(
            'entityId',
            $this->_objectManager->create(\Magento\Review\Model\Rating\Entity::class)->getIdByCode('product')
        );
    }
}
