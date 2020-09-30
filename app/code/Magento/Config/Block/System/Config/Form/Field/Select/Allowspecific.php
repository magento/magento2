<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System configuration shipping methods allow all countries select
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Config\Block\System\Config\Form\Field\Select;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Allowspecific extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * Allowspecific constructor.
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data, $secureRenderer);
        $this->secureRenderer = $secureRenderer;
    }

    /**
     * Add additional Javascript code
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $elementId = $this->getHtmlId();
        $countryListId = $this->_getSpecificCountryElementId();
        $useDefaultElementId = $countryListId . '_inherit';

        $elementJavaScript = <<<HTML
//<![CDATA[
document.getElementById('{$elementId}').addEventListener('change', function(event) {
    var isCountrySpecific = event.target.value == 1,
        specificCountriesElement = document.getElementById('{$countryListId}'),
        // 'Use Default' checkbox of the related county list UI element
        useDefaultElement = document.getElementById('{$useDefaultElementId}');

    if (isCountrySpecific) {
        // enable related country select only if its 'Use Default' checkbox is absent or is unchecked
        specificCountriesElement.disabled = useDefaultElement ? useDefaultElement.checked : false;
    } else {
        // disable related country select if all countries are used
        specificCountriesElement.disabled = true;
    }
});
//]]>
HTML;

        return $this->secureRenderer->renderTag('script', [], $elementJavaScript, false) .
            parent::getAfterElementHtml();
    }

    /**
     * Return generated html.
     *
     * @return string
     */
    public function getHtml()
    {
        if (!$this->getValue() || 1 != $this->getValue()) {
            $element = $this->getForm()->getElement($this->_getSpecificCountryElementId());
            $element->setDisabled('disabled');
        }
        return parent::getHtml();
    }

    /**
     * Return country specific element id.
     *
     * @return string
     */
    protected function _getSpecificCountryElementId()
    {
        return substr($this->getId(), 0, strrpos($this->getId(), 'allowspecific')) . 'specificcountry';
    }
}
