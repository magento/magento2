<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorization\Model;

use Magento\Framework\ObjectManager\Helper\Composite as CompositeHelper;

/**
 * User context.
 *
 * This class is not implementing standard composite pattern and will not invoke all of its children.
 * Instead, it will try to find the first suitable child and return its result.
 *
 * @api
 * @since 2.0.0
 */
class CompositeUserContext implements \Magento\Authorization\Model\UserContextInterface
{
    /**
     * @var UserContextInterface[]
     * @since 2.0.0
     */
    protected $userContexts = [];

    /**
     * @var UserContextInterface|bool
     * @since 2.0.0
     */
    protected $chosenUserContext;

    /**
     * Register user contexts.
     *
     * @param CompositeHelper $compositeHelper
     * @param UserContextInterface[] $userContexts
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function add(UserContextInterface $userContext)
    {
        $this->userContexts[] = $userContext;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUserId()
    {
        return $this->getUserContext() ? $this->getUserContext()->getUserId() : null;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUserType()
    {
        return $this->getUserContext() ? $this->getUserContext()->getUserType() : null;
    }

    /**
     * Retrieve user context
     *
     * @return UserContextInterface|bool False if none of the registered user contexts can identify user type
     * @since 2.0.0
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
