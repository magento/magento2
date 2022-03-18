<?php

namespace Magento\AdminAdobeIms\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Disabled extends Field
{
    /** @var ImsConfig */
    private ImsConfig $imsConfig;

    /**
     * @param Context $context
     * @param SecureHtmlRenderer $secureRenderer
     * @param ImsConfig $imsConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        SecureHtmlRenderer $secureRenderer,
        ImsConfig $imsConfig,
        array $data = []
    ) {
        parent::__construct($context, $data, $secureRenderer);
        $this->imsConfig = $imsConfig;
    }

    /**
     * Return an empty string for the render if our module is enabled
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        if ($this->imsConfig->enabled() === false) {
            return parent::render($element);
        }
        return '';
    }
}
