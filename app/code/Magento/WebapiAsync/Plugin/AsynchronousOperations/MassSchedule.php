<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Plugin\AsynchronousOperations;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\AsynchronousOperations\Model\MassSchedule as SubjectMassSchedule;

/**
 * Plugin to validate anonymous request for asynchronous operations contains group id.
 */
class MassSchedule
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     *
     * @param AuthorizationInterface|null $authorization
     */
    public function __construct(
        AuthorizationInterface $authorization = null
    ) {
        $objectManager = ObjectManager::getInstance();
        $this->authorization = $authorization ?? $objectManager->get(AuthorizationInterface::class);
    }

    /**
     * Validate groupId for anonymous request
     *
     * @param SubjectMassSchedule $subjectMassSchedule
     * @param string $topic
     * @param array $entitiesArray
     * @return void
     * @throws AuthorizationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePublishMass(
        SubjectMassSchedule $subjectMassSchedule,
        string $topic,
        array $entitiesArray
    ): void {
        foreach ($entitiesArray as $entityParams) {
            foreach ($entityParams as $customer) {
                if ($customer instanceof CustomerInterface) {
                    $groupId = $customer->getGroupId();
                    if (isset($groupId) && !$this->authorization->isAllowed(self::ADMIN_RESOURCE)) {
                        $params = ['resources' => self::ADMIN_RESOURCE];
                        throw new AuthorizationException(
                            __("The consumer isn't authorized to access %resources.", $params)
                        );
                    }
                }
            }
        }
    }
}
