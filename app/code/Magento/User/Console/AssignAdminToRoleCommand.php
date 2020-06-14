<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Console;

use InvalidArgumentException;
use Magento\Authorization\Model\GetAdminRolesList;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\User\Model\AssignUserToRole;
use Magento\User\Model\GetAdminUserList;
use Magento\User\Model\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class AssignAdminToRoleCommand assigns role to user
 */
class AssignAdminToRoleCommand extends Command
{
    private const USER_NAME_OPTION = 'username';
    private const ROLE_ID_OPTION = 'role-id';

    /**
     * @var AssignUserToRole
     */
    private $assignUserToRoleService;

    /**
     * @var GetAdminRolesList
     */
    private $getAdminRolesListService;

    /**
     * @var GetAdminUserList
     */
    private $getAdminUserListService;

    /**
     * @param AssignUserToRole $assignUserToRoleService
     * @param GetAdminRolesList $getAdminRolesListService
     * @param GetAdminUserList $getAdminUserListService
     * @param string|null $name
     */
    public function __construct(
        AssignUserToRole $assignUserToRoleService,
        GetAdminRolesList $getAdminRolesListService,
        GetAdminUserList $getAdminUserListService,
        string $name = null
    ) {
        parent::__construct($name);
        $this->assignUserToRoleService = $assignUserToRoleService;
        $this->getAdminRolesListService = $getAdminRolesListService;
        $this->getAdminUserListService = $getAdminUserListService;
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName("admin:user:assign-role");
        $this->setDescription("Assigns user to role");
        $this->setDefinition($this->getOptionsList());

        parent::configure();
    }

    /**
     * Assigns role to user
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws AlreadyExistsException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->assignUserToRoleService->execute(
            $input->getOption(self::USER_NAME_OPTION),
            (int)$input->getOption(self::ROLE_ID_OPTION)
        );
        $output->writeln("User assigned to the role.");
    }

    /**
     * @inheritDoc
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        if (!$input->getOption(self::USER_NAME_OPTION)) {
            $question = new Question('<question>Admin username:</question> ', '');
            $userList = $this->getAdminUserListService->execute();
            $question->setValidator(function ($value) use ($userList) {
                /** @var User $user */
                foreach ($userList as $user) {
                    if ($user->getUserName() === $value) {
                        return $value;
                    }
                }
                throw new InvalidArgumentException(sprintf(AssignUserToRole::USER_DOES_NOT_EXISTS_ERROR_MSG, $value));
            });

            $input->setOption(
                self::USER_NAME_OPTION,
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption(self::ROLE_ID_OPTION)) {
            $question = new Question('<question>Role ID:</question> ', '');
            $rolesList = $this->getAdminRolesListService->execute();
            $question->setValidator(function ($value) use ($rolesList) {
                foreach ($rolesList as $role) {
                    if ($role['role_id'] === $value) {
                        return $value;
                    }
                }
                throw new InvalidArgumentException(sprintf(AssignUserToRole::ROLE_DOES_NOT_EXISTS_ERROR_MSG, $value));
            });

            $input->setOption(
                self::ROLE_ID_OPTION,
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Get list of arguments for the command
     *
     * @return InputOption[]
     */
    public function getOptionsList(): array
    {
        return [
            new InputOption(
                self::USER_NAME_OPTION,
                null,
                InputOption::VALUE_REQUIRED,
                '(Required) Admin Username'
            ),
            new InputOption(
                self::ROLE_ID_OPTION,
                null,
                InputOption::VALUE_REQUIRED,
                '(Required) Role ID'
            )
        ];
    }
}
