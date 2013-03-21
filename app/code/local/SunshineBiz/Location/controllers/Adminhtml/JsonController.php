<?php

/**
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Adminhtml_JsonController extends Mage_Adminhtml_JsonController {

    public function regionAreaAction() {
        $arrRes = array();

        $regionId = $this->getRequest()->getParam('parent');
        $arrRegions = Mage::getResourceModel('SunshineBiz_Location_Model_Resource_Area_Collection')
                ->addRegionFilter($regionId)
                ->load()
                ->toOptionArray();

        if (!empty($arrRegions)) {
            foreach ($arrRegions as $region) {
                $arrRes[] = $region;
            }
        }

        $this->getResponse()->setBody(Mage::helper('Mage_Core_Helper_Data')->jsonEncode($arrRes));
    }

}