<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Controller\Adminhtml\Json;

use Magento\Backend\App\Action;
use Magento\Customer\Model\Vat;

class IsEuCountry extends Action
{
    /**
     * Return JSON-encoded value (true/false) as per country is in EU country list or not
     *
     * @return string
     */
    public function execute()
    {
        $isEuCountry = false;
        $countryCode = $this->getRequest()->getParam('countryCode') ?? null;

        if (!empty($countryCode)) {

            if ($this->_objectManager->create(Vat::class)->isCountryInEU($countryCode)) {
                $isEuCountry = true;
            }
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($isEuCountry)
        );
    }
}
