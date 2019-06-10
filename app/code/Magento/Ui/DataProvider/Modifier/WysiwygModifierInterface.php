<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Modifier;

/**
 * @api
 * @since 101.1.0
 */
interface WysiwygModifierInterface
{
    /**
     * Provide editor name
     * For example tmce3 or tmce4
     *
     * @return array
     * @since 101.1.0
     */
    public function getEditorName();

    /**
     * @param array $meta
     * @return array
     * @since 101.1.0
     */
    public function modifyMeta(array $meta);
}
