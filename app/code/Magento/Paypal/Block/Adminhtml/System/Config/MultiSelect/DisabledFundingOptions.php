<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Block\Adminhtml\System\Config\MultiSelect;

use Magento\Paypal\Block\Adminhtml\System\Config\Field\Enable\AbstractEnable;
use Magento\Paypal\Model\Config\StructurePlugin;
use Magento\Backend\Block\Template\Context;
use Magento\Paypal\Model\Config;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class DisabledFundingOptions
 */
class DisabledFundingOptions extends AbstractEnable
{
    /**
     * @var Config
     */
    private $config;

    /**
     * DisabledFundingOptions constructor.
     * @param Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Render country field considering request parameter
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if (!$this->isSelectedMerchantCountry('US')) {
            $fundingOptions = $element->getValues();
            $element->setValues($this->filterValuesForPaypalCredit($fundingOptions));
        }
        return parent::render($element);
    }

    /**
     * Getting the name of a UI attribute
     *
     * @return string
     */
    protected function getDataAttributeName(): string
    {
        return 'disable-funding-options';
    }

    /**
     * Filters array for CREDIT
     *
     * @param array $options
     * @return array
     */
    private function filterValuesForPaypalCredit($options): array
    {
        return array_filter($options, function ($opt) {
            return ($opt['value'] !== 'CREDIT');
        });
    }

    /**
     * Checks for chosen Merchant country from the config/url
     *
     * @param string $country
     * @return bool
     */
    private function isSelectedMerchantCountry(string $country): bool
    {
        $merchantCountry = $this->getRequest()->getParam(StructurePlugin::REQUEST_PARAM_COUNTRY)
            ?: $this->config->getMerchantCountry();
        return $merchantCountry === $country;
    }
}
