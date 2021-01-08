<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Console\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class StoreCreateCommand
 * Command for create a new the configured store
 */
class StoreCreateCommand extends Command
{
    const INPUT_ARGUMENT_NAME = 'name';
    const INPUT_ARGUMENT_CODE = 'code';
    const INPUT_ARGUMENT_IS_ACTIVE = 'is_active';
    const INPUT_ARGUMENT_SORT_ORDER = 'sort_order';

    const INPUT_OPTION_GROUP = 'group_id';

    /**
     * @var \Magento\Store\Api\Data\StoreInterfaceFactory
     */
    private $storeFactory;

    /**
     *
     * @var \Magento\Store\Model\CreateStore
     */
    private $createStore;

    /**
     *
     * @var \Magento\Store\Model\GetDefaultStoreGroup
     */
    private $getDefaultStoreGroup;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     *
     * @param \Magento\Store\Api\Data\StoreInterfaceFactory $storeFactory
     * @param \Magento\Store\Model\CreateStore $createStore
     * @param \Magento\Store\Model\GetDefaultStoreGroup $getDefaultStoreGroup
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param string $name
     */
    public function __construct(
        \Magento\Store\Api\Data\StoreInterfaceFactory $storeFactory,
        \Magento\Store\Model\CreateStore $createStore,
        \Magento\Store\Model\GetDefaultStoreGroup $getDefaultStoreGroup,
        \Magento\Framework\Filter\FilterManager $filterManager,
        $name = null
    ) {
        $this->storeFactory = $storeFactory;
        $this->createStore = $createStore;
        $this->getDefaultStoreGroup = $getDefaultStoreGroup;
        $this->filterManager = $filterManager;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('store:create')
            ->setDescription('Create new store view (see list: bin/magento store:list)')
            ->addArgument(self::INPUT_ARGUMENT_NAME, InputArgument::REQUIRED, 'Put the store view name you want to create')
            ->addArgument(self::INPUT_ARGUMENT_CODE, InputArgument::REQUIRED, 'Put the store view code')
            ->addArgument(
                self::INPUT_ARGUMENT_IS_ACTIVE,
                InputArgument::OPTIONAL,
                'Status (enable/disable)',
                true
            )
            ->addArgument(
                self::INPUT_ARGUMENT_SORT_ORDER,
                InputArgument::OPTIONAL,
                'Sort Order',
                0
            )
        ;

        $this->addOption(
            self::INPUT_OPTION_GROUP,
            'g',
            InputOption::VALUE_OPTIONAL,
            'Group ID (php bin/magento store:list).'
        );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $defaultGroupId = $input->getOption(self::INPUT_OPTION_GROUP);
            if ($defaultGroupId === null) {
                /** @var \Magento\Store\Api\Data\GroupInterface $defaultGroup */
                $defaultGroup = $this->getDefaultStoreGroup->execute();
                $defaultGroupId = $defaultGroup->getId();
            }
            $defaultGroupId = (int) $defaultGroupId;
            $data = [
                self::INPUT_OPTION_GROUP => (int) $input->getOption(
                    self::INPUT_OPTION_GROUP
                ),
                self::INPUT_ARGUMENT_NAME => (string) $input->getArgument(
                    self::INPUT_ARGUMENT_NAME
                ),
                self::INPUT_ARGUMENT_CODE => (string) $input->getArgument(
                    self::INPUT_ARGUMENT_CODE
                ),
                self::INPUT_ARGUMENT_IS_ACTIVE  => (bool) $input->getArgument(
                    self::INPUT_ARGUMENT_IS_ACTIVE
                ),
                self::INPUT_ARGUMENT_SORT_ORDER => (int) $input->getArgument(
                    self::INPUT_ARGUMENT_SORT_ORDER
                )
            ];
            $data[self::INPUT_ARGUMENT_NAME] = $this->filterManager->removeTags(
                $data[self::INPUT_ARGUMENT_NAME]
            );
            $data[self::INPUT_ARGUMENT_CODE] = $this->filterManager->removeTags(
                $data[self::INPUT_ARGUMENT_CODE]
            );

            /** @var \Magento\Store\Model\Storev $storeModel */
            $storeModel = $this->storeFactory->create(['data' => $data]);

            $this->createStore->create($storeModel);

            $io->success('You created the store view.');

            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $io->comment($e->getTraceAsString());
            }

            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
