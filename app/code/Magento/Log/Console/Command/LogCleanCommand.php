<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Log\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command for displaying status of Magento logs
 */
class LogCleanCommand extends AbstractLogCommand
{
    /**
     * Name of input option
     */
    const INPUT_KEY_DAYS = 'days';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_DAYS,
                null,
                InputOption::VALUE_REQUIRED,
                'Save log for specified number of days',
                '1'
            ),
        ];
        $this->setName('log:clean')
            ->setDescription('Cleans Logs')
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errorMsg = 'Invalid value for option "' . self::INPUT_KEY_DAYS
            . '". It should be a whole number greater than 0.';
        $days = $input->getOption(self::INPUT_KEY_DAYS);
        if (!is_numeric($days) || (strpos($days, '.') !== false)) {
            $output->writeln('<error>' . $errorMsg . '</error>');
            return;
        }
        $days = (int) $days;
        if ($days <= 0) {
            $output->writeln('<error>' . $errorMsg . '</error>');
            return;
        }
        /** @var \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableConfig */
        $mutableConfig = $this->objectManager->create('Magento\Framework\App\Config\MutableScopeConfigInterface');
        $mutableConfig->setValue(
            \Magento\Log\Model\Log::XML_LOG_CLEAN_DAYS,
            $days,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        /** @var \Magento\Log\Model\LogFactory $logFactory */
        $logFactory = $this->objectManager->create('Magento\Log\Model\LogFactory');
        /** @var \Magento\Log\Model\Log $model */
        $model = $logFactory->create();
        $model->clean();
        $output->writeln('<info>' . 'Log cleaned.' . '</info>');
    }
}
