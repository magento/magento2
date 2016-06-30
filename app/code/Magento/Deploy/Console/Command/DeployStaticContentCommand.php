<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Console\Command;

use Magento\Framework\App\Utility\Files;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validator\Locale;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\State;

/**
 * Deploy static content command
 */
class DeployStaticContentCommand extends Command
{
    /**
     * Key for dry-run option
     */
    const DRY_RUN_OPTION = 'dry-run';

    /**
     * Key for languages parameter
     */
    const LANGUAGE_OPTION = 'languages';

    /**
     * @var Locale
     */
    private $validator;

    /**
     * Factory to get object manager
     *
     * @var ObjectManagerFactory
     */
    private $objectManagerFactory;

    /**
     * object manager to create various objects
     *
     * @var ObjectManagerInterface
     *
     */
    private $objectManager;

    /** @var \Magento\Framework\App\State */
    private $appState;

    /**
     * Inject dependencies
     *
     * @param ObjectManagerFactory $objectManagerFactory
     * @param Locale $validator
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        Locale $validator,
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManagerFactory = $objectManagerFactory;
        $this->validator = $validator;
        $this->objectManager = $objectManager;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('setup:static-content:deploy')
            ->setDescription('Deploys static view files')
            ->setDefinition([
                new InputOption(
                    self::DRY_RUN_OPTION,
                    '-d',
                    InputOption::VALUE_NONE,
                    'If specified, then no files will be actually deployed.'
                ),
                new InputArgument(
                    self::LANGUAGE_OPTION,
                    InputArgument::IS_ARRAY,
                    'List of languages you want the tool populate files for.',
                    ['en_US']
                ),
            ]);
        parent::configure();
    }

    /**
     * @return \Magento\Framework\App\State
     * @deprecated
     */
    private function getAppState()
    {
        if (null === $this->appState) {
            $this->appState = $this->objectManager->get('Magento\Framework\App\State');
        }
        return $this->appState;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->getAppState()->getMode() !== State::MODE_PRODUCTION) {
            throw new LocalizedException(
                __(
                    "Deploy static content is applicable only for production mode.\n"
                    . "Please use command 'bin/magento deploy:mode:set production' for set up production mode."
                )
            );
        }

        $options = $input->getOptions();

        $languages = $input->getArgument(self::LANGUAGE_OPTION);
        foreach ($languages as $lang) {

            if (!$this->validator->isValid($lang)) {
                throw new \InvalidArgumentException(
                    $lang . ' argument has invalid value, please run info:language:list for list of available locales'
                );
            }
        }

        // run the deployment logic
        $filesUtil = $this->objectManager->create(Files::class);

        $deployer = $this->objectManager->create(
            'Magento\Deploy\Model\Deployer',
            ['filesUtil' => $filesUtil, 'output' => $output, 'isDryRun' => $options[self::DRY_RUN_OPTION]]
        );
        return $deployer->deploy($this->objectManagerFactory, $languages);
    }
}
