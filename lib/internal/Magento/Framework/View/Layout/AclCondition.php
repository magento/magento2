<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;

/**
 * Check that user is allowed to watch resource with given acl resource..
 * @since 2.2.0
 */
class AclCondition implements VisibilityConditionInterface
{
    /**
     * Unique name.
     */
    const NAME = 'acl';

    /**
     * @var \Magento\Framework\AuthorizationInterface
     * @since 2.2.0
     */
    private $authorization;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @since 2.2.0
     */
    public function __construct(\Magento\Framework\AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function isVisible(array $arguments)
    {
        return $this->authorization->isAllowed($arguments['acl']);
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getName()
    {
        return self::NAME;
    }
}
