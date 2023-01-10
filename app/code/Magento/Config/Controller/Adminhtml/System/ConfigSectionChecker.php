<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Controller\Adminhtml\System;

use Laminas\Permissions\Acl\Exception\InvalidArgumentException;
use Magento\Framework\Exception\NotFoundException;

/**
 * @deprecated 101.0.0 - unused class.
 * @see \Magento\Config\Model\Config\Structure\Element\Section::isAllowed()
 */
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
                // phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new \Exception('');
            }
            return true;
        } catch (InvalidArgumentException $e) {
            // phpcs:ignore Magento2.Exceptions.ThrowCatch
            throw new NotFoundException(__('Page not found.'));
            // phpcs:ignore Magento2.Exceptions.ThrowCatch
        } catch (\Exception $e) {
            return false;
        }
    }
}
