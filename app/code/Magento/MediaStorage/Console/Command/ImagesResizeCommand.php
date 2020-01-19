<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\MediaStorage\Service\ImageResize;
use Magento\MediaStorage\Service\ImageResizeScheduler;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\ProgressBarFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Magento\Catalog\Model\ResourceModel\Product\Image as ProductImage;

/**
 * Resizes product images according to theme view definitions.
 */
class ImagesResizeCommand extends Command
{
    /**
     * Asynchronous image resize mode
     */
    const ASYNC_RESIZE = 'async';

    /**
     * @var ImageResizeScheduler
     */
    private $imageResizeScheduler;

    /**
     * @var ImageResize
     */
    private $imageResize;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ProgressBarFactory
     */
    private $progressBarFactory;

    /**
     * @var ProductImage
     */
    private $productImage;

    /**
     * @param State $appState
     * @param ImageResize $imageResize
     * @param ImageResizeScheduler $imageResizeScheduler
     * @param ProgressBarFactory $progressBarFactory
     * @param ProductImage $productImage
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        State $appState,
        ImageResize $imageResize,
        ImageResizeScheduler $imageResizeScheduler,
        ProgressBarFactory $progressBarFactory,
        ProductImage $productImage
    ) {
        parent::__construct();
        $this->appState = $appState;
        $this->imageResize = $imageResize;
        $this->imageResizeScheduler = $imageResizeScheduler;
        $this->progressBarFactory = $progressBarFactory;
        $this->productImage = $productImage;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('catalog:images:resize')
            ->setDescription('Creates resized product images')
            ->setDefinition($this->getOptionsList());
    }

    /**
     * Image resize command options list
     *
     * @return array
     */
    private function getOptionsList() : array
    {
        return [
            new InputOption(
                self::ASYNC_RESIZE,
                'a',
                InputOption::VALUE_NONE,
                'Resize image in asynchronous mode'
            ),
        ];
    }

    /**
     * @inheritdoc
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $input->getOption(self::ASYNC_RESIZE) ?
            $this->executeAsync($output) : $this->executeSync($output);

        return $result;
    }

    /**
     * Run resize in synchronous mode
     *
     * @param OutputInterface $output
     * @return int
     */
    private function executeSync(OutputInterface $output): int
    {
        try {
            $errors = [];
            $this->appState->setAreaCode(Area::AREA_GLOBAL);
            $generator = $this->imageResize->resizeFromThemes();

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
            return Cli::RETURN_FAILURE;
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

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Schedule asynchronous image resizing
     *
     * @param OutputInterface $output
     * @return int
     */
    private function executeAsync(OutputInterface $output): int
    {
        try {
            $errors = [];
            $this->appState->setAreaCode(Area::AREA_GLOBAL);

            /** @var ProgressBar $progress */
            $progress = $this->progressBarFactory->create(
                [
                    'output' => $output,
                    'max' => $this->productImage->getCountUsedProductImages()
                ]
            );
            $progress->setFormat(
                "%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s% \t| <info>%message%</info>"
            );

            if ($output->getVerbosity() !== OutputInterface::VERBOSITY_NORMAL) {
                $progress->setOverwrite(false);
            }

            $productImages = $this->productImage->getUsedProductImages();
            foreach ($productImages as $image) {
                $result = $this->imageResizeScheduler->schedule($image['filepath']);

                if (!$result) {
                    $errors[$image['filepath']] = 'Error image scheduling: ' . $image['filepath'];
                }
                $progress->setMessage($image['filepath']);
                $progress->advance();
            }
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            // we must have an exit code higher than zero to indicate something was wrong
            return Cli::RETURN_FAILURE;
        }

        $output->write(PHP_EOL);
        if (count($errors)) {
            $output->writeln("<info>Product images resized with errors:</info>");
            foreach ($errors as $error) {
                $output->writeln("<error>{$error}</error>");
            }
        } else {
            $output->writeln("<info>Product images scheduled successfully</info>");
        }

        return Cli::RETURN_SUCCESS;
    }
}
