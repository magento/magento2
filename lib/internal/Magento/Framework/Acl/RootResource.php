<?php
/**
 * Root ACL Resource
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl;

/**
 * @api
 * @since 2.0.0
 */
class RootResource
{
    /**
     * Root resource id
     *
     * @var string
     * @since 2.0.0
     */
    protected $_identifier;

    /**
     * @param string $identifier
     * @since 2.0.0
     */
    public function __construct($identifier)
    {
        $this->_identifier = $identifier;
    }

    /**
     * Retrieve root resource id
     *
     * @return string
     * @since 2.0.0
     */
    public function getId()
    {
        return $this->_identifier;
    }
}
