<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
     * Modifies the meta
     *
     * @param array $meta
     *
     * @return array
     * @since 101.1.0
     */
    public function modifyMeta(array $meta);
}
