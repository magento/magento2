<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;

/**
 * Check that user is allowed to watch resource with given acl resource..
 */
class AclCondition implements VisibilityConditionInterface
{
    /**
     * Unique name.
     */
    const NAME = 'acl';

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $authorization;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(\Magento\Framework\AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * @inheritdoc
     */
    public function isVisible(array $arguments)
    {
        return $this->authorization->isAllowed($arguments['acl']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
