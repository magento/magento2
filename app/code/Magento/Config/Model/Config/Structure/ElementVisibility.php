<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure;

use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 * Contains list of classes which implement ElementVisibilityInterface for checking of visibility of form elements.
 */
class ElementVisibility implements ElementVisibilityInterface
{
    /**
     * @var ElementVisibilityInterface[]
     */
    private $visibility = [];

    /**
     * @param ElementVisibilityInterface[] $visibility
     * @throws ConfigurationMismatchException
     */
    public function __construct(array $visibility = [])
    {
        foreach ($visibility as $name => $item) {
            if (!$item instanceof ElementVisibilityInterface) {
                throw new ConfigurationMismatchException(
                    __('%1 is not instance on %2', $name, ElementVisibilityInterface::class)
                );
            }
        }

        $this->visibility = $visibility;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden($path)
    {
        foreach ($this->visibility as $element) {
            if ($element->isHidden($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isDisabled($path)
    {
        foreach ($this->visibility as $element) {
            if ($element->isDisabled($path)) {
                return true;
            }
        }

        return false;
    }
}
