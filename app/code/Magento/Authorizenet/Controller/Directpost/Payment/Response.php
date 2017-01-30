<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Directpost\Payment;

use Magento\Authorizenet\Model\DirectpostFactory;

class Response extends \Magento\Authorizenet\Controller\Directpost\Payment
{
    /** @var \Magento\Authorizenet\Model\DirectpostFactory */
    private $directpostFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Authorizenet\Helper\DataFactory $dataFactory
     * @param \Magento\Authorizenet\Model\DirectpostFactory|null $directpostFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Authorizenet\Helper\DataFactory $dataFactory,
        \Magento\Authorizenet\Model\DirectpostFactory $directpostFactory = null
    ) {
        parent::__construct($context, $coreRegistry, $dataFactory);
        $this->directpostFactory = $directpostFactory ? : $this->_objectManager->create(DirectpostFactory::class);
    }

    /**
     * Response action.
     * Action for Authorize.net SIM Relay Request.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->_responseAction('frontend', $this->directpostFactory->create());
    }
}
