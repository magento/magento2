<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Setup\Model\BackupRollback;
use Magento\Framework\Backup\Factory;

/**
 * Command prints list of available backup files
 */
class InfoBackupsListCommand extends Command
{
    /**
     * File
     *
     * @var File
     */
    private $file;

    /**
     * Filesystem Directory List
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        DirectoryList $directoryList,
        File $file
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('info:backups:list')
            ->setDescription('Prints list of available backup files');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = $this->getHelperSet()->get('table');
        $table->setHeaders(['Backup Filename', 'Backup Type']);
        $backupsDir = $this->directoryList->getPath(DirectoryList::VAR_DIR)
            . '/' . BackupRollback::DEFAULT_BACKUP_DIRECTORY;
        $output->writeln('<info>Showing backup files in ' . $backupsDir . ' ...</info>');
        if ($this->file->isExists($backupsDir)) {
            $contents = $this->file->readDirectoryRecursively($backupsDir);
            foreach ($contents as $path) {
                $partsOfPath = explode('/', str_replace('\\', '/', $path));
                $fileName = $partsOfPath[count($partsOfPath) - 1];
                if (!$this->startsWith($fileName, '.')) {
                    $withoutExt = explode('.', $fileName);
                    $fileNameParts = explode('_', $withoutExt[0]);
                    if (in_array(Factory::TYPE_MEDIA, $fileNameParts)) {
                        $table->addRow([$fileName, Factory::TYPE_MEDIA]);
                    } elseif (in_array(Factory::TYPE_DB, $fileNameParts)) {
                        $table->addRow([$fileName, Factory::TYPE_DB]);
                    } elseif (in_array(Factory::TYPE_FILESYSTEM, $fileNameParts)) {
                        $table->addRow([$fileName, Factory::TYPE_FILESYSTEM]);
                    }
                }
            }
        }

        $table->render($output);
    }

    /**
     * Search backwards starting from haystack length characters from the end
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    private function startsWith($haystack, $needle)
    {
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
}
