<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Controller\Adminhtml\Json;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;

class IsEuCountry extends Action
{
    /**
     * @var EuCountryProviderInterface
     */
    private $euCountryProvider;

    /**
     * @param Context $context
     * @param EuCountryProviderInterface|null $euCountryProvider
     */
    public function __construct(
        Context $context,
        ?EuCountryProviderInterface $euCountryProvider = null
    ) {
        parent::__construct($context);
        $this->euCountryProvider = $euCountryProvider
            ?? ObjectManager::getInstance()->get(EuCountryProviderInterface::class);
    }

    /**
     * Return JSON-encoded value (true/false) as per country is in EU country list or not
     *
     * @return string
     */
    public function execute()
    {
        $isEuCountry = false;
        $countryCode = $this->getRequest()->getParam('countryCode');
        $isEuCountry = $this->euCountryProvider->isEuCountry($countryCode);

        return $this->getResponse()->representJson($isEuCountry);
    }
}
