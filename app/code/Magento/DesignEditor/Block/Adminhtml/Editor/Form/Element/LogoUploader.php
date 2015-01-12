<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element;

/**
 * Form element renderer to display logo uploader element for VDE
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class LogoUploader extends \Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\ImageUploader
{
    /**
     * Control type
     */
    const CONTROL_TYPE = 'logo-uploader';

    /**
     * @var bool Ability to upload multiple files by default is disabled for logo
     */
    protected $_multipleFiles = false;
}
