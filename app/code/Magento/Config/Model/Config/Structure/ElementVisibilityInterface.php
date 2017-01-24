<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure;

/**
 * Checks visibility of form elements by their paths in the structure.
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
     * @param string $path
     * @return bool
     */
    public function isDisabled($path);

    /**
     * Check whether form element is hidden from form by path.
     *
     * @param string $path
     * @return bool
     */
    public function isHidden($path);
}
