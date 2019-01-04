<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Plugin;

use Magento\Framework\Session\SessionManagerInterface;
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
     * @var \Magento\Framework\Session\SessionManagerInterface|\Magento\Backend\Model\Auth\Session
     */
    private $session;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     */
    public function __construct(
        SessionManagerInterface $session
    ) {
        $this->session = $session;
    }

    /**
     * @param \Magento\Customer\Model\Metadata\AttributeMetadataCache $subject
     * @param string $entityType
     * @param string $suffix
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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
