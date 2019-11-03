<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Email\Container;

class NullIdentity extends Container implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getEmailCopyTo()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getCopyMethod()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getGuestTemplateId()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getTemplateId()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getEmailIdentity()
    {
        return '';
    }
}
