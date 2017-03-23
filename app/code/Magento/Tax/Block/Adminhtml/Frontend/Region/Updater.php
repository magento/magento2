<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Block\Adminhtml\Frontend\Region;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Updater extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    ) {
        $this->_directoryHelper = $directoryHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);

        $js = '<script>
              require(["prototype", "mage/adminhtml/form"], function(){
               updater = new RegionUpdater("tax_defaults_country", "none", "tax_defaults_region", %s, "nullify");
               if(updater.lastCountryId) {
                   var tmpRegionId = $("tax_defaults_region").value;
                   var tmpCountryId = updater.lastCountryId;
                   updater.lastCountryId=false;
                   updater.update();
                   updater.lastCountryId = tmpCountryId;
                   $("tax_defaults_region").value = tmpRegionId;
               } else {
                   updater.update();
               }
                });
               </script>';

        $html .= sprintf($js, $this->_directoryHelper->getRegionJson());
        return $html;
    }
}
