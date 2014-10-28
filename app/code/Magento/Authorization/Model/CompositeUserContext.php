<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        if (is_null($this->chosenUserContext)) {
            /** @var UserContextInterface $userContext */
            foreach ($this->userContexts as $userContext) {
                if ($userContext->getUserType() && !is_null($userContext->getUserId())) {
                    $this->chosenUserContext = $userContext;
                    break;
                }
            }
            if (is_null($this->chosenUserContext)) {
                $this->chosenUserContext = false;
            }
        }
        return $this->chosenUserContext;
    }
}
