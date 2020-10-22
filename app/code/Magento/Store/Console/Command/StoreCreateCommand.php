<?php
namespace Magento\Store\Console\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 *
 */
class StoreCreateCommand extends Command
{
    const INPUT_ARGUMENT_NAME = 'name';
    const INPUT_ARGUMENT_CODE = 'code';
    const INPUT_ARGUMENT_IS_ACTIVE = 'is_active';
    const INPUT_ARGUMENT_SORT_ORDER = 'sort_order';

    const INPUT_OPTION_GROUP = 'group_id';

    /**
     *
     * @var \Magento\Store\Model\StoreCreateManagement
     */
    private $storeCreateManagement;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     *
     * @param \Magento\Store\Model\StoreCreateManagement $storetoreCreateManagement
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param string                                  $name
     */
    public function __construct(
        \Magento\Store\Model\StoreCreateManagement $storeCreateManagement,
        \Magento\Framework\Filter\FilterManager $filterManager,
        $name = null
    ) {
        $this->storeCreateManagement = $storeCreateManagement;
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
        $defaultGroup = $this->storeCreateManagement->getDefaultGroupId();

        $this->addOption(
            self::INPUT_OPTION_GROUP,
            'g',
            InputOption::VALUE_OPTIONAL,
            'Group ID (php bin/magento store:list).',
            $defaultGroup
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
            $groupId = (int) $input->getOption(self::INPUT_OPTION_GROUP);
            $storeViewName = (string) $input->getArgument(self::INPUT_ARGUMENT_NAME);
            $storeViewCode = (string) $input->getArgument(self::INPUT_ARGUMENT_CODE);
            $isActive = (bool) $input->getArgument(self::INPUT_ARGUMENT_IS_ACTIVE);
            $sortOrder = (int) $input->getArgument(self::INPUT_ARGUMENT_SORT_ORDER);

            $data = [
                self::INPUT_OPTION_GROUP        => $groupId,
                self::INPUT_ARGUMENT_NAME       => $storeViewName,
                self::INPUT_ARGUMENT_CODE       => $storeViewCode,
                self::INPUT_ARGUMENT_IS_ACTIVE  => $isActive,
                self::INPUT_ARGUMENT_SORT_ORDER => $sortOrder
            ];
            $data[self::INPUT_ARGUMENT_NAME] = $this->filterManager->removeTags(
                $data[self::INPUT_ARGUMENT_NAME]
            );
            $data[self::INPUT_ARGUMENT_CODE] = $this->filterManager->removeTags(
                $data[self::INPUT_ARGUMENT_CODE]
            );

            $this->storeCreateManagement->create($data);

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
