<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Block\Adminhtml\Frontend\Region;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Updater extends Field
{
    /**
     * @var DirectoryHelper
     */
    protected $_directoryHelper;

    /**
     * @param Context $context
     * @param DirectoryHelper $directoryHelper
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        DirectoryHelper $directoryHelper,
        array $data = [],
        private ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->_directoryHelper = $directoryHelper;
        parent::__construct($context, $data);
        $this->secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
    }

    /**
     * Return element html.
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);

        $js = 'require(["prototype", "mage/adminhtml/form"], function(){
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
                });';

        $scriptString = sprintf($js, $this->_directoryHelper->getRegionJson());

        $html .= /* @noEscape */ $this->secureRenderer->renderTag('script', [], $scriptString, false);

        return $html;
    }
}
