<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tinymce3\Model\Config\Source\Wysiwyg;

/**
 * Class Editor provides configuration value for TinyMCE3 editor
 * @deprecated use as configuration value tinymce4 path: mage/adminhtml/wysiwyg/tiny_mce/tinymce4Adapter
 */
class Editor
{
    /**
     * Configuration value for TinyMCE3 editor
     * @var string
     */
    const WYSIWYG_EDITOR_CONFIG_VALUE = 'Magento_Tinymce3/tinymce3Adapter';
}
