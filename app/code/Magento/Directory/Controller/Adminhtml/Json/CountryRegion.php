<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Directory\Controller\Adminhtml\Json;

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
                'Magento\Directory\Model\Resource\Region\Collection'
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
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($arrRes)
        );
    }
}
