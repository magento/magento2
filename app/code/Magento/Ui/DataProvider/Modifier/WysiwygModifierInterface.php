<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Modifier;

/**
 * @api
 */
interface WysiwygModifierInterface
{
    /**
     * Provide editor name
     * For example tmce3 or tmce4
     *
     * @return array
     */
    public function getEditorName();

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta);
}
