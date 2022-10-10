<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Disabled extends Field
{
    /** @var ImsConfig */
    private ImsConfig $adminImsConfig;

    /**
     * @param Context $context
     * @param SecureHtmlRenderer $secureRenderer
     * @param ImsConfig $adminImsConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        SecureHtmlRenderer $secureRenderer,
        ImsConfig $adminImsConfig,
        array $data = []
    ) {
        parent::__construct($context, $data, $secureRenderer);
        $this->adminImsConfig = $adminImsConfig;
    }

    /**
     * Return an empty string for the render if our module is enabled
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        if ($this->adminImsConfig->enabled() === false) {
            return parent::render($element);
        }
        return '';
    }
}
