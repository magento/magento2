<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;

/**
 * Class AclCondition.
 */
class AclCondition implements VisibilityConditionInterface
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $authorization;

    /**
     * AclCondition constructor.
     *
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(\Magento\Framework\AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * Validate logical condition for ui component
     * If validation passed block will be displayed
     *
     * @param array $arguments Attributes from element node.
     *
     * @return bool
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
        return 'acl';
    }
}
