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
     * Provide editor name for example tmce4
     *
     * @return array
     * @since 101.1.0
     */
    public function getEditorName();

    /**
     * Modifies meta
     *
     * @param array $meta
     *
     * @return array
     * @since 101.1.0
     */
    public function modifyMeta(array $meta);
}
