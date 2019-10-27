<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Magento\MediaStorage\Service\ImageResize;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\ProgressBarFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Resizes product images according to theme view definitions.
 *
 * @package Magento\MediaStorage\Console\Command
 */
class ImagesResizeCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var ImageResize
     */
    private $resize;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ProgressBarFactory
     */
    private $progressBarFactory;

    /**
     * @param State $appState
     * @param ImageResize $resize
     * @param ObjectManagerInterface $objectManager
     * @param ProgressBarFactory $progressBarFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        State $appState,
        ImageResize $resize,
        ObjectManagerInterface $objectManager,
        ProgressBarFactory $progressBarFactory = null
    ) {
        parent::__construct();
        $this->resize = $resize;
        $this->appState = $appState;
        $this->progressBarFactory = $progressBarFactory
            ?: ObjectManager::getInstance()->get(ProgressBarFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('catalog:images:resize')
            ->setDescription('Creates resized product images');
    }

    /**
     * @inheritdoc
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $errors = [];
            $this->appState->setAreaCode(Area::AREA_GLOBAL);
            $generator = $this->resize->resizeFromThemes();

            /** @var ProgressBar $progress */
            $progress = $this->progressBarFactory->create(
                [
                    'output' => $output,
                    'max' => $generator->current()
                ]
            );
            $progress->setFormat(
                "%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s% \t| <info>%message%</info>"
            );

            if ($output->getVerbosity() !== OutputInterface::VERBOSITY_NORMAL) {
                $progress->setOverwrite(false);
            }

            while ($generator->valid()) {
                $resizeInfo = $generator->key();
                $error = $resizeInfo['error'];
                $filename = $resizeInfo['filename'];

                if ($error !== '') {
                    $errors[$filename] = $error;
                }

                $progress->setMessage($filename);
                $progress->advance();
                $generator->next();
            }
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $output->write(PHP_EOL);
        if (count($errors)) {
            $output->writeln("<info>Product images resized with errors:</info>");
            foreach ($errors as $error) {
                $output->writeln("<error>{$error}</error>");
            }
        } else {
            $output->writeln("<info>Product images resized successfully</info>");
        }

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
