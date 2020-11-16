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
 * @since 100.0.2
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
     * @inheritDoc
     */
    public function getUserId()
    {
        return $this->getUserContext() ? $this->getUserContext()->getUserId() : null;
    }

    /**
     * @inheritDoc
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
        if (!$this->chosenUserContext) {
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
