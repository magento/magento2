<?php
/**
 * Composer Magento Installer
 */

namespace MagentoHackathon\Composer\Magento\Deploystrategy;

/**
 * Hardlink deploy strategy
 */
class Link extends DeploystrategyAbstract
{
    /**
     * Creates a hardlink with lots of error-checking
     *
     * @param string $source
     * @param string $dest
     * @return bool
     * @throws \ErrorException
     */
    public function createDelegate($source, $dest)
    {
        $sourcePath = $this->getSourceDir() . '/' . $this->removeTrailingSlash($source);
        $destPath = $this->getDestDir() . '/' . $this->removeTrailingSlash($dest);


        // Create all directories up to one below the target if they don't exist
        $destDir = dirname($destPath);
        if (!file_exists($destDir)) {
            mkdir($destDir, 0777, true);
        }

        // Handle source to dir link,
        // e.g. Namespace_Module.csv => app/locale/de_DE/
        if (file_exists($destPath) && is_dir($destPath)) {
            if (basename($sourcePath) === basename($destPath)) {
                // copy/link each child of $sourcePath into $destPath
                foreach (new \DirectoryIterator($sourcePath) as $item) {
                    $item = (string) $item;
                    if (!strcmp($item, '.') || !strcmp($item, '..')) {
                        continue;
                    }
                    $childSource = $source . '/' . $item;
                    $this->create($childSource, substr($destPath, strlen($this->getDestDir())+1));
                }
                return true;
            } else {
                $destPath .= '/' . basename($source);
                return $this->create($source, substr($destPath, strlen($this->getDestDir())+1));
            }
        }

        // From now on $destPath can't be a directory, that case is already handled

        // If file exists and force is not specified, throw exception unless FORCE is set
        if (file_exists($destPath)) {
            if ($this->isForced()) {
                unlink($destPath);
            } else {
                throw new \ErrorException("Target $dest already exists (set extra.magento-force to override)");
            }
        }

        // File to file
        if (!is_dir($sourcePath)) {
            if (is_dir($destPath)) {
                $destPath .= '/' . basename($sourcePath);
            }
            return link($sourcePath, $destPath);
        }

        // Copy dir to dir
        // First create destination folder if it doesn't exist
        if (file_exists($destPath)) {
            $destPath .= '/' . basename($sourcePath);
        }
        mkdir($destPath, 0777, true);

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sourcePath),
            \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $item) {
            $subDestPath = $destPath . '/' . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (! file_exists($subDestPath)) {
                    mkdir($subDestPath, 0777, true);
                }
            } else {
                link($item, $subDestPath);
            }
            if (!is_readable($subDestPath)) {
                throw new \ErrorException("Could not create $subDestPath");
            }
        }

        return true;
    }
}
