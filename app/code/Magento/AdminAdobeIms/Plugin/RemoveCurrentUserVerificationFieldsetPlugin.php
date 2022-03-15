<?php

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\Form\Element\Fieldset;

class RemoveCurrentUserVerificationFieldsetPlugin
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
     * @param AbstractForm $subject
     * @param Fieldset $result
     * @param string $elementId
     * @param array $config
     * @param bool|string|null $after
     * @param bool $isAdvanced
     * @return Fieldset
     */
    public function afterAddFieldset(
        AbstractForm $subject,
        Fieldset $result,
        $elementId,
        $config,
        $after = false,
        $isAdvanced = false)
    : Fieldset {
        if ($elementId !== 'current_user_verification_fieldset') {
            return $result;
        }
        if ($this->imsConfig->enabled() !== true) {
            return $result;
        }

        $subject->removeField($elementId);
        return $result;
    }
}
