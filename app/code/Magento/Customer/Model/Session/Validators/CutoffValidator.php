<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Session\Validators;

use Magento\Customer\Model\ResourceModel\Customer as ResourceCustomer;
use Magento\Customer\Model\ResourceModel\Visitor as ResourceVisitor;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Phrase;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Session\ValidatorInterface;
use Magento\Framework\Session\Generic;

/**
 * Session Validator
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CutoffValidator implements ValidatorInterface
{
    /**
     * @var ResourceCustomer
     */
    private $customerResource;

    /**
     * @var ResourceVisitor
     */
    private $visitorResource;

    /**
     * @var Generic
     */
    private $visitorSession;

    /**
     * Cutoff validator constructor.
     *
     * @param ResourceCustomer $customerResource
     * @param ResourceVisitor $visitorResource
     * @param Generic $visitorSession
     */
    public function __construct(
        ResourceCustomer $customerResource,
        ResourceVisitor $visitorResource,
        Generic $visitorSession
    ) {
        $this->customerResource = $customerResource;
        $this->visitorResource = $visitorResource;
        $this->visitorSession = $visitorSession;
    }

    /**
     * Validate session
     *
     * @param SessionManagerInterface $session
     * @return void
     * @throws SessionException
     */
    public function validate(SessionManagerInterface $session): void
    {
        try {
            $visitor = $this->visitorSession->getVisitorData();
            if ($visitor !== null
                && array_key_exists('customer_id', $visitor)
                && array_key_exists('visitor_id', $visitor)
            ) {
                $cutoff = $this->customerResource->findSessionCutOff((int) $visitor['customer_id']);
                $sessionCreationTime = $this->visitorResource->fetchCreatedAt((int) $visitor['visitor_id']);
                if (isset($cutoff, $sessionCreationTime) && $cutoff > $sessionCreationTime) {
                    throw new SessionException(
                        new Phrase('The session has expired, please login again.')
                    );
                }
            }
        } catch (SessionException $e) {
            $session->destroy(['clear_storage' => false]);
            // throw core session exception
            throw $e;
        }
    }
}
