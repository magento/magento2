<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Controller\Adminhtml\System;

use Magento\Framework\Exception\NotFoundException;

/**
 * @deprecated 2.2.0 - unused class.
 * @see \Magento\Config\Model\Config\Structure\Element\Section::isAllowed()
 * @since 2.0.0
 */
class ConfigSectionChecker
{
    /**
     * @var \Magento\Config\Model\Config\Structure
     * @since 2.0.0
     */
    protected $_configStructure;

    /**
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @since 2.0.0
     */
    public function __construct(\Magento\Config\Model\Config\Structure $configStructure)
    {
        $this->_configStructure = $configStructure;
    }

    /**
     * Check if specified section allowed in ACL
     *
     * Will forward to deniedAction(), if not allowed.
     *
     * @param string $sectionId
     * @throws \Exception
     * @return bool
     * @throws NotFoundException
     * @since 2.0.0
     */
    public function isSectionAllowed($sectionId)
    {
        try {
            if (false == $this->_configStructure->getElement($sectionId)->isAllowed()) {
                throw new \Exception('');
            }
            return true;
        } catch (\Zend_Acl_Exception $e) {
            throw new NotFoundException(__('Page not found.'));
        } catch (\Exception $e) {
            return false;
        }
    }
}
