<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorization\Model;

use Magento\Framework\ObjectManager\Helper\Composite as CompositeHelper;

/**
 * Composite user context (implements composite pattern).
 */
class CompositeUserContext implements \Magento\Authorization\Model\UserContextInterface
{
    /**
     * @var UserContextInterface[]
     */
    protected $userContexts = [];

    /**
     * @var UserContextInterface|bool
     */
    protected $chosenUserContext;

    /**
     * Register user contexts.
     *
     * @param CompositeHelper $compositeHelper
     * @param UserContextInterface[] $userContexts
     */
    public function __construct(CompositeHelper $compositeHelper, $userContexts = [])
    {
        $userContexts = $compositeHelper->filterAndSortDeclaredComponents($userContexts);
        foreach ($userContexts as $userContext) {
            $this->add($userContext['type']);
        }
    }

    /**
     * Add user context.
     *
     * @param UserContextInterface $userContext
     * @return CompositeUserContext
     */
    protected function add(UserContextInterface $userContext)
    {
        $this->userContexts[] = $userContext;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        return $this->getUserContext() ? $this->getUserContext()->getUserId() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserType()
    {
        return $this->getUserContext() ? $this->getUserContext()->getUserType() : null;
    }

    /**
     * Retrieve user context
     *
     * @return UserContextInterface|bool False if none of the registered user contexts can identify user type
     */
    protected function getUserContext()
    {
        if ($this->chosenUserContext === null) {
            /** @var UserContextInterface $userContext */
            foreach ($this->userContexts as $userContext) {
                if ($userContext->getUserType() && $userContext->getUserId() !== null) {
                    $this->chosenUserContext = $userContext;
                    break;
                }
            }
            if ($this->chosenUserContext === null) {
                $this->chosenUserContext = false;
            }
        }
        return $this->chosenUserContext;
    }
}
