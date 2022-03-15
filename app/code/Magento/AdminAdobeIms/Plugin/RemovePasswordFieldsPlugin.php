<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Fieldset;

class RemovePasswordFieldsPlugin
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
     * @param Fieldset $subject
     * @param AbstractElement $result
     * @param string $elementId
     * @param string $type
     * @param array $config
     * @param bool $after
     * @param bool $isAdvanced
     * @return AbstractElement
     */
    public function afterAddField(
        Fieldset $subject,
        AbstractElement $result,
        $elementId,
        $type,
        $config,
        $after = false,
        $isAdvanced = false
    ): AbstractElement
    {
        if ($this->imsConfig->enabled() !== true) {
            return $result;
        }

        if (
            $subject->getId() === 'base_fieldset'
            && (
                $elementId === 'password'
                || $elementId === 'confirmation'
            )
        ) {
            $subject->removeField($elementId);
        }
        return $result;
    }
}
