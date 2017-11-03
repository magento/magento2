<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Modifier\Wysiwyg;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\WysiwygModifierInterface;

/**
 * Class WysiwygModifierComposite
 *
 * @api
 *
 */
class WysiwygModifierComposite implements WysiwygModifierInterface
{
    /**
     * @var array
     */
    private $modifiers;

    /**
     * @var
     */
    private $currentEditorName;

    /**
     * WysiwygModifierComposite constructor.
     * @param array $modifiers
     * @param string $currentEditorName
     */
    public function __construct(array $modifiers, $currentEditorName)
    {
        $this->modifiers = $modifiers;
        $this->currentEditorName = $currentEditorName;
    }

    /**
     * @return string
     */
    public function getEditorName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        /** @var WysiwygModifierInterface $modifier */
        foreach ($this->modifiers as $modifier) {
            // Apply only modifiers, editor of which is the same as current one
            if ($modifier->getEditorName() === $this->currentEditorName) {
                $meta = $modifier->modifyMeta($meta);
            }
        }

        return $meta;
    }
}
