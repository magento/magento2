<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Plugin;

use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Model\Metadata\AttributeMetadataCache;

/**
 * Class AttributeMetadataCacheByAdminRole
 *
 * Fixing issue when cache is the same for each admin user role
 * but restrictions to attributes can be different.
 * Fixed by adding _ROLEID_int to suffix.
 */
class AttributeMetadataCacheByAdminRole
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $session;

    /**
     * @param \Magento\Backend\Model\Auth\Session $session
     */
    public function __construct(
        Session $session
    ) {
        $this->session = $session;
    }

    /**
     * @param \Magento\Customer\Model\Metadata\AttributeMetadataCache $subject
     * @param string $entityType
     * @param string $suffix
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeLoad(AttributeMetadataCache $subject, $entityType, $suffix = '')
    {
        if ($this->session->isLoggedIn()) {
            $roleId = $this->session->getUser()->getRole()->getId();
            $suffix = $suffix . '_ROLEID_' . $roleId;
        }

        return [$entityType, $suffix];
    }

    /**
     * @param \Magento\Customer\Model\Metadata\AttributeMetadataCache $subject
     * @param string $entityType
     * @param array $attributes
     * @param string $suffix
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(AttributeMetadataCache $subject, $entityType, array $attributes, $suffix = '')
    {
        if ($this->session->isLoggedIn()) {
            $roleId = $this->session->getUser()->getRole()->getId();
            $suffix = $suffix . '_ROLEID_' . $roleId;
        }

        return [$entityType, $attributes, $suffix];
    }
}
