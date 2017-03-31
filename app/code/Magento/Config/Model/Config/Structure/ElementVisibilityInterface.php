<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure;

/**
 * Checks visibility status of form elements on Stores > Settings > Configuration page in Admin Panel
 * by their paths in the system.xml structure.
 */
interface ElementVisibilityInterface
{
    /**#@+
     * Constants of statuses for form elements.
     */
    const HIDDEN = 'hidden';
    const DISABLED = 'disabled';
    /**#@-*/

    /**
     * Check whether form element is disabled by path.
     *
     * @param string $path The path of form element in the system.xml structure
     * @return bool
     */
    public function isDisabled($path);

    /**
     * Check whether form element is hidden in form by path.
     *
     * @param string $path The path of form element in the system.xml structure
     * @return bool
     */
    public function isHidden($path);
}
