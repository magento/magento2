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
     * Provide editor name for example tmce4
     *
     * @return array
     */
    public function getEditorName();

    /**
     * Modifies the meta
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta);
}
