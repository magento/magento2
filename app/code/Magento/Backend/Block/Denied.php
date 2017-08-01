<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block;

/**
 * @api
 * @since 2.0.0
 */
class Denied extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     * @since 2.0.0
     */
    protected $_authSession;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->_authSession = $authSession;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function hasAvailableResources()
    {
        $user = $this->_authSession->getUser();
        if ($user && $user->getHasAvailableResources()) {
            return true;
        }
        return false;
    }
}
