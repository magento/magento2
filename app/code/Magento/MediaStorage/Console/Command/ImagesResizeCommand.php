<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\MediaStorage\Service\ImageResize;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Magento\Framework\ObjectManagerInterface;

/**
 * Image resize console command
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
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param State $appState
     * @param ImageResize $resize
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        State $appState,
        ImageResize $resize,
        ObjectManagerInterface $objectManager
    ) {
        parent::__construct();
        $this->resize = $resize;
        $this->appState = $appState;
        $this->objectManager = $objectManager;
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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode(Area::AREA_GLOBAL);

            /** @var ProgressBar $progress */
            $progress = $this->objectManager->create(
                ProgressBar::class,
                [
                    'output' => $output,
                    'max' => $this->resize->getCountForResize()
                ]
            );
            $progress->setFormat(
                "%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s% \t| <info>%message%</info>"
            );

            if ($output->getVerbosity() !== OutputInterface::VERBOSITY_NORMAL) {
                $progress->setOverwrite(false);
            }

            $generator = $this->resize->resizeFromThemes();

            foreach ($generator as $filename => $result) {
                $progress->setMessage($filename);
                $progress->advance();

                if ($result instanceof \Exception) {
                    $output->writeln(''); // blank line for aligning error messages
                    $output->writeln("<error>{$result->getMessage()}</error>");
                }
            }
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $output->write(PHP_EOL);
        $output->writeln("<info>Product images resized successfully</info>");
    }
}
