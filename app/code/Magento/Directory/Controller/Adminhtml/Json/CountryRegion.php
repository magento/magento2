<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Controller\Adminhtml\Json;

/**
 * Class \Magento\Directory\Controller\Adminhtml\Json\CountryRegion
 *
 */
class CountryRegion extends \Magento\Backend\App\Action
{
    /**
     * Return JSON-encoded array of country regions
     *
     * @return string
     */
    public function execute()
    {
        $arrRes = [];

        $countryId = $this->getRequest()->getParam('parent');
        if (!empty($countryId)) {
            $arrRegions = $this->_objectManager->create(
                \Magento\Directory\Model\ResourceModel\Region\Collection::class
            )->addCountryFilter(
                $countryId
            )->load()->toOptionArray();

            if (!empty($arrRegions)) {
                foreach ($arrRegions as $region) {
                    $arrRes[] = $region;
                }
            }
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($arrRes)
        );
    }
}
