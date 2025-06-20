<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Console\Command;

use Magento\Email\Model\AbstractTemplate;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\VariableCompatibilityChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;

/**
 * Scan DB templates for directive incompatibilities
 */
class DatabaseTemplateCompatibilityCommand extends Command
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $templateCollection;

    /**
     * @var VariableCompatibilityChecker
     */
    private VariableCompatibilityChecker $compatibilityChecker;

    /**
     * @var bool
     */
    protected bool $hasErrors = false;

    /**
     * Constructor
     *
     * @param VariableCompatibilityChecker $compatibilityChecker
     * @param CollectionFactory $templateCollection
     * @param string $name
     */
    public function __construct(
        VariableCompatibilityChecker $compatibilityChecker,
        CollectionFactory $templateCollection,
        ?string $name = null
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
        $this->setName('dev:email:override-compatibility-check')
            ->setDescription('Scans email template overrides for potential variable usage compatibility issues');
    }

    /**
     * @inheritDoc
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

    /**
     * Check the given template for compatibility issues
     *
     * @param AbstractTemplate $template
     * @param OutputInterface $output
     */
    protected function checkTemplate(AbstractTemplate $template, OutputInterface $output): void
    {
        $errors = $this->compatibilityChecker->getCompatibilityIssues($template->getTemplateText());
        $templateName = $template->getTemplateCode();
        if (!empty($errors)) {
            $this->hasErrors = true;
            $output->writeln(
                '<error>Template "' . $templateName . '" has the following compatibility issues:</error>'
            );
            $this->renderErrors($output, $errors);
        }
        $errors = $this->compatibilityChecker->getCompatibilityIssues($template->getTemplateSubject());
        if (!empty($errors)) {
            $this->hasErrors = true;
            $output->writeln(
                '<error>Template "' . $templateName . '" subject has the following compatibility issues:</error>'
            );
            $this->renderErrors($output, $errors);
        }
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
            $error = str_replace(PHP_EOL, PHP_EOL . '   ', $error ?? '');
            $output->writeln(
                '<error> - ' . $error . '</error>'
            );
        }
        $output->writeln('');
    }
}
