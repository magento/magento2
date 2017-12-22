<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Modifier\Wysiwyg;

use Magento\Ui\DataProvider\Modifier\WysiwygModifierInterface;

/**
 * Class DefaultWysiwygSettings
 */
class DefaultWysiwygSettings implements WysiwygModifierInterface
{
    const EDITOR_NAME = "tmce4";

    /**
     * @return string
     */
    public function getEditorName()
    {
        return self::EDITOR_NAME;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
