<?php

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\System\Account\Edit\Form as VerificationForm;

class RemoveCurrentUserVerificationFieldsPlugin
{
    /** @var ImsConfig */
    private ImsConfig $imsConfig;

    /**
     * @param ImsConfig $imsConfig
     */
    public function __construct(
        ImsConfig $imsConfig
    ) {
        $this->imsConfig = $imsConfig;
    }

    /**
     * @param Form $subject
     * @param callable $proceed
     * @param AbstractElement $element
     * @param bool $after
     */
    public function aroundAddElement(Form $subject, callable $proceed, AbstractElement $element, $after = false)
    {
        $elementId = $element->getId();
        if (
            $elementId !== 'current_user_verification_fieldset'
            || $elementId !== VerificationForm::IDENTITY_VERIFICATION_PASSWORD_FIELD
        ) {
            return $proceed($element, $after);
        }
        if ($this->imsConfig->enabled() !== true) {
            return $proceed($element, $after);
        }
    }
}
