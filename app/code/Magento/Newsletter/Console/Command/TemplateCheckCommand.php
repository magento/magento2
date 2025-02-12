<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Console\Command;

use Magento\Email\Console\Command\DatabaseTemplateCompatibilityCommand;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory as EmailCollectionFactory;
use Magento\Newsletter\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\VariableCompatibilityChecker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;

/**
 * Scan DB templates for directive incompatibilities
 */
class TemplateCheckCommand extends DatabaseTemplateCompatibilityCommand
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $templateCollection;

    /**
     * Constructor
     *
     * @param VariableCompatibilityChecker $compatibilityChecker
     * @param EmailCollectionFactory $templateCollection
     * @param CollectionFactory $newsletterCollectionFactory
     * @param string|null $name
     */
    public function __construct(
        VariableCompatibilityChecker $compatibilityChecker,
        EmailCollectionFactory $templateCollection,
        CollectionFactory $newsletterCollectionFactory,
        ?string $name = null
    ) {
        parent::__construct($compatibilityChecker, $templateCollection, $name);

        $this->templateCollection = $newsletterCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('dev:email:newsletter-compatibility-check')
            ->setDescription('Scans newsletter templates for potential variable usage compatibility issues');
    }

    /**
     * Executes compatibility checker command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collection = $this->templateCollection->create();
        $collection->load();

        $this->hasErrors = false;
        foreach ($collection as $template) {
            $this->checkTemplate($template, $output);
        }

        if (!$this->hasErrors) {
            $output->writeln('<info>No errors detected</info>');
        }
        return $this->hasErrors ? Cli::RETURN_FAILURE : Cli::RETURN_SUCCESS;
    }
}
