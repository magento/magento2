<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Backup\Factory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Setup\BackupRollback;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $backupsDir = $this->directoryList->getPath(DirectoryList::VAR_DIR)
            . '/' . BackupRollback::DEFAULT_BACKUP_DIRECTORY;
        if ($this->file->isExists($backupsDir)) {
            $output->writeln(
                '<info>Showing backup files in ' . $backupsDir
                . ' (first part of the filename is the time it was taken) ...</info>'
            );
            $table = $this->getHelperSet()->get('table');
            $table->setHeaders(['Backup Filename', 'Backup Type']);
            $contents = $this->file->readDirectoryRecursively($backupsDir);
            if (empty($contents)) {
                $output->writeln('<info>No backup files found.</info>');
                return;
            }
            foreach ($contents as $path) {
                $partsOfPath = explode('/', str_replace('\\', '/', $path));
                $fileName = $partsOfPath[count($partsOfPath) - 1];
                // if filename starts with '.' skip, e.g. '.git'
                if (!$this->startsWith($fileName, '.')) {
                    $filenameWithoutExtension = explode('.', $fileName);
                    // actually first part of $filenameWithoutExtension contains only the filename without extension
                    // and filename contains the type of backup separated by '_'
                    $fileNameParts = explode('_', $filenameWithoutExtension[0]);
                    if (in_array(Factory::TYPE_MEDIA, $fileNameParts)) {
                        $table->addRow([$fileName, Factory::TYPE_MEDIA]);
                    } elseif (in_array(Factory::TYPE_DB, $fileNameParts)) {
                        $table->addRow([$fileName, Factory::TYPE_DB]);
                    } elseif (in_array(Factory::TYPE_FILESYSTEM, $fileNameParts)) {
                        $table->addRow([$fileName, Factory::TYPE_FILESYSTEM]);
                    }
                }
            }
            $table->render($output);
        } else {
            $output->writeln('<info>No backup files found.</info>');
        }
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
