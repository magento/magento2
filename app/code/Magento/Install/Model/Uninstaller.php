<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Install\Model;

use Magento\Framework\App\Filesystem;
use Magento\Framework\Filesystem\FilesystemException;

/**
 * A model for uninstalling Magento application
 */
class Uninstaller
{
    /**
     * A service for cleaning up directories
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * A service for recreating database
     *
     * @var Installer\Db\Mysql4
     */
    private $db;

    /**
     * Logger
     *
     * @var \Zend_Log
     */
    private $log;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param Installer\Db\Mysql4 $db
     * @param \Zend_Log $log
     */
    public function __construct(
        Filesystem $filesystem,
        Installer\Db\Mysql4 $db,
        \Zend_Log $log
    ) {
        $this->filesystem = $filesystem;
        $this->db = $db;
        $this->log = $log;
    }

    /**
     * Uninstalls the application
     *
     * @return void
     */
    public function uninstall()
    {
        $this->log('Starting uninstall');
        $this->recreateDatabase();
        $this->log('File system cleanup:');
        $this->deleteDirContents(Filesystem::VAR_DIR);
        $this->deleteDirContents(Filesystem::STATIC_VIEW_DIR);
        $this->deleteLocalXml();
        $this->log('Uninstall complete.');
    }

    /**
     * Log output
     *
     * @param string $message
     * @param int $level
     * @return void
     */
    private function log($message, $level = \Zend_Log::INFO)
    {
        $this->log->log($message, $level);
    }

    /**
     * Deletes the database and creates it again
     *
     * @return void
     */
    private function recreateDatabase()
    {
        $connectionData = $this->db->getConnectionData();
        if (empty($connectionData['dbName'])) {
            $this->log('No database connection defined - skipping cleanup');
        } else {
            $this->log("Recreating database '{$connectionData['dbName']}'");
            $this->db->cleanUpDatabase();
        }
    }

    /**
     * Removes contents of a directory
     *
     * @param string $type
     * @return void
     */
    private function deleteDirContents($type)
    {
        $dir = $this->filesystem->getDirectoryWrite($type);
        $dirPath = $dir->getAbsolutePath();
        foreach ($dir->read() as $path) {
            if (preg_match('/^\./', $path)) {
                continue;
            }
            $this->log("{$dirPath}{$path}");
            try {
                $dir->delete($path);
            } catch (FilesystemException $e) {
                $this->log($e->getMessage());
            }
        }
    }

    /**
     * Removes deployment configuration
     *
     * @return void
     */
    protected function deleteLocalXml()
    {
        $configDir = $this->filesystem->getDirectoryWrite(Filesystem::CONFIG_DIR);
        $localXml = "{$configDir->getAbsolutePath()}local.xml";
        if (!$configDir->isFile('local.xml')) {
            $this->log("The file '{$localXml}' doesn't exist - skipping cleanup");
            return;
        }
        try {
            $this->log($localXml);
            $configDir->delete('local.xml');
        } catch (FilesystemException $e) {
            $this->log($e->getMessage());
        }
    }
}
