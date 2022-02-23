<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Console\Command;

use Magento\Email\Model\ResourceModel\Template\Collection;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template;
use Magento\Email\Model\Template\CompatibilityChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;

/**
 * Scan DB templates for directive incompatibilities
 */
class DbTemplateCheckCommand extends Command
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $templateCollection;

    /**
     * @var CompatibilityChecker
     */
    private CompatibilityChecker $compatibilityChecker;

    public function __construct(
        CollectionFactory $templateCollection,
        CompatibilityChecker $compatibilityChecker,
        string $name = null
    ) {
        parent::__construct($name);
        $this->templateCollection = $templateCollection;
        $this->compatibilityChecker = $compatibilityChecker;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('setup:email-override-compatibility-check')
            ->setDescription('Scans email template overrides for potential compatibility issues');

        parent::configure();
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
        /** @var Collection $collection */
        $collection = $this->templateCollection->create();
        $collection->load();

        $hasErrors = false;
        foreach ($collection as $template) {
            /** @var Template $template */
            $errors = $this->compatibilityChecker->getCompatibilityIssues($template->getTemplateText());
            if (!empty($errors)) {
                $hasErrors = true;
                $templateName = $template->getTemplateCode();
                $output->writeln(
                    '<error>Template "' . $templateName . '" has the following compatibility issues:</error>'
                );
                $this->renderErrors($output, $errors);
            }
            $errors = $this->compatibilityChecker->getCompatibilityIssues($template->getTemplateSubject());
            if (!empty($errors)) {
                $hasErrors = true;
                $templateName = $template->getTemplateCode();
                $output->writeln(
                    '<error>Template "' . $templateName . '" subject has the following compatibility issues:</error>'
                );
                $this->renderErrors($output, $errors);
            }
        }

        if (!$hasErrors) {
            $output->writeln('<info>No errors detected</info>');
        }
        return $hasErrors ? Cli::RETURN_FAILURE : Cli::RETURN_SUCCESS;
    }

    /**
     * Render given errors
     *
     * @param OutputInterface $output
     * @param array $errors
     */
    private function renderErrors(OutputInterface $output, array $errors): void
    {
        foreach ($errors as $error) {
            $error = str_replace(PHP_EOL, PHP_EOL . '   ', $error);
            $output->writeln(
                '<error> - ' . $error . '</error>'
            );
        }
        $output->writeln('');
    }
}
