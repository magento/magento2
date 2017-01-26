<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure;

use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 * Contains list of classes which implement ElementVisibilityInterface for
 * checking of visibility of form elements on Stores > Settings > Configuration page in Admin Panel.
 */
class ElementVisibilityComposite implements ElementVisibilityInterface
{
    /**
     * List of objects which implements ElementVisibilityInterface for
     * checking of visibility of form elements on Configuration page.
     *
     * @var ElementVisibilityInterface[]
     */
    private $visibility = [];

    /**
     * @param ElementVisibilityInterface[] $visibility List of objects which implement ElementVisibilityInterface.
     * @throws ConfigurationMismatchException It is thrown if some object from list $visibility
     * implements the wrong interface.
     */
    public function __construct(array $visibility = [])
    {
        foreach ($visibility as $name => $item) {
            if (!$item instanceof ElementVisibilityInterface) {
                throw new ConfigurationMismatchException(
                    __(
                        '%1: Instance of %2 is expected, got %3 instead',
                        $name,
                        ElementVisibilityInterface::class,
                        get_class($item)
                    )
                );
            }
        }

        $this->visibility = $visibility;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
