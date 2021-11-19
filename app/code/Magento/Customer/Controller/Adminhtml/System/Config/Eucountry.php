<?php
/**
 * * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\Vat;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\App\Action;

class Eucountry extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Vat
     */
    protected $customerVat;

    /**
     * @param Context     $context
     * @param JsonFactory $resultJsonFactory
     * @param Vat         $customerVat
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Check whether vat is valid
     *
     * @return Json
     */
    public function execute()
    {
        $countryCode = $this->getRequest()->getParam('countryCode');

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData(
            $this->_objectManager->get(Vat::class)->isCountryInEU($countryCode)
        );
    }
}
