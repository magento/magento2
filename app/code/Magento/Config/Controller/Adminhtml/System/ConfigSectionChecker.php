<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Controller\Adminhtml\System;

use Magento\Framework\Exception\NotFoundException;

class ConfigSectionChecker
{
    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    protected $_configStructure;

    /**
     * @param \Magento\Config\Model\Config\Structure $configStructure
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
