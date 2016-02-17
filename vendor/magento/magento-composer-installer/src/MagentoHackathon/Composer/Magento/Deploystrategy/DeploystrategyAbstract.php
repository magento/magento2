<?php
/**
 * Composer Magento Installer
 */

namespace MagentoHackathon\Composer\Magento\Deploystrategy;

/**
 * Abstract deploy strategy
 */
abstract class DeploystrategyAbstract
{
    /**
     * The path mappings to map project's directories to magento's directory structure
     *
     * @var array
     */
    protected $mappings = array();

    /**
     * The current mapping of the deployment iteration
     *
     * @var array
     */
    protected $currentMapping = array();

    /**
     * The List of entries which files should not get deployed
     * 
     * @var array
     */
    protected $ignoredMappings = array();


    /**
     * The magento installation's base directory
     *
     * @var string
     */
    protected $destDir;

    /**
     * The module's base directory
     *
     * @var string
     */
    protected $sourceDir;

    /**
     * If set overrides existing files
     *
     * @var bool
     */
    protected $isForced = false;

    /**
     * Constructor
     *
     * @param string $sourceDir
     * @param string $destDir
     */
    public function __construct($sourceDir, $destDir)
    {
        $this->destDir = $destDir;
        $this->sourceDir = $sourceDir;
    }

    /**
     * Executes the deployment strategy for each mapping
     *
     * @return \MagentoHackathon\Composer\Magento\Deploystrategy\DeploystrategyAbstract
     */
    public function deploy()
    {
        foreach ($this->getMappings() as $data) {
            list ($source, $dest) = $data;
            $this->setCurrentMapping($data);
            $this->create($source, $dest);
        }
        return $this;
    }

    /**
     * Removes the module's files in the given path from the target dir
     *
     * @return \MagentoHackathon\Composer\Magento\Deploystrategy\DeploystrategyAbstract
     */
    public function clean()
    {
        foreach ($this->getMappings() as $data) {
            list ($source, $dest) = $data;
            $this->remove($source, $dest);
            $this->rmEmptyDirsRecursive(dirname($dest), $this->getDestDir());
        }
        return $this;
    }

    /**
     * Returns the destination dir of the magento module
     *
     * @return string
     */
    protected function getDestDir()
    {
        return $this->destDir;
    }

    /**
     * Returns the current path of the extension
     *
     * @return mixed
     */
    protected function getSourceDir()
    {
        return $this->sourceDir;
    }

    /**
     * If set overrides existing files
     *
     * @return bool
     */
    public function isForced()
    {
        return $this->isForced;
    }

    /**
     * Setter for isForced property
     *
     * @param bool $forced
     */
    public function setIsForced($forced = true)
    {
        $this->isForced = (bool) $forced;
    }

    /**
     * Returns the path mappings to map project's directories to magento's directory structure
     *
     * @return array
     */
    public function getMappings()
    {
        return $this->mappings;
    }

    /**
     * Sets path mappings to map project's directories to magento's directory structure
     *
     * @param array $mappings
     */
    public function setMappings(array $mappings)
    {
        $this->mappings = $mappings;
    }

    /**
     * Gets the current mapping used on the deployment iteration
     *
     * @return array
     */
    public function getCurrentMapping()
    {
        return $this->currentMapping;
    }

    /**
     * Sets the current mapping used on the deployment iteration
     *
     * @param array $mapping
     */
    public function setCurrentMapping($mapping)
    {
        $this->currentMapping = $mapping;
    }


    /**
     * sets the current ignored mappings
     * 
     * @param $ignoredMappings
     */
    public function setIgnoredMappings($ignoredMappings)
    {
        $this->ignoredMappings = $ignoredMappings;
    }

    /**
     * gets the current ignored mappings
     * 
     * @return array
     */
    public function getIgnoredMappings()
    {
        return $this->ignoredMappings;
    }


    /**
     * @param string $destination
     *
     * @return bool
     */
    protected  function isDestinationIgnored($destination)
    {
        $destination = '/'.$destination;
        $destination = str_replace('/./','/', $destination);
        $destination = str_replace('//','/', $destination);
        foreach($this->ignoredMappings as $ignored){
            if( 0 === strpos($ignored,$destination) ){
                return true;
            }
        }
        return false;
    }

    /**
     * Add a key value pair to mapping
     */
    public function addMapping($key, $value)
    {
        $this->mappings[] = array($key, $value);
    }

    protected function removeTrailingSlash($path)
    {
       return rtrim($path, ' \\/');
    }

    /**
     * Normalize mapping parameters using a glob wildcard.
     *
     * Delegate the creation of the module's files in the given destination.
     *
     * @param string $source
     * @param string $dest
     * @throws \ErrorException
     * @return bool
     */
    public function create($source, $dest)
    {
        if($this->isDestinationIgnored($dest)){
            return;
        }
        
        $sourcePath = $this->getSourceDir() . '/' . $this->removeTrailingSlash($source);
        $destPath = $this->getDestDir() . '/' . $dest;

        /* List of possible cases, keep around for now, might come in handy again

        Assume app/etc exists, app/etc/a does not exist unless specified differently

        dir app/etc/a/ --> link app/etc/a to dir
        dir app/etc/a  --> link app/etc/a to dir
        dir app/etc/   --> link app/etc/dir to dir
        dir app/etc    --> link app/etc/dir to dir

        dir/* app/etc     --> for each dir/$file create a target link in app/etc
        dir/* app/etc/    --> for each dir/$file create a target link in app/etc
        dir/* app/etc/a   --> for each dir/$file create a target link in app/etc/a
        dir/* app/etc/a/  --> for each dir/$file create a target link in app/etc/a

        file app/etc    --> link app/etc/file to file
        file app/etc/   --> link app/etc/file to file
        file app/etc/a  --> link app/etc/a to file
        file app/etc/a  --> if app/etc/a is a file throw exception unless force is set, in that case rm and see above
        file app/etc/a/ --> link app/etc/a/file to file regardless if app/etc/a exists or not

        */

        // Create target directory if it ends with a directory separator
        if (! file_exists($destPath) && in_array(substr($destPath, -1), array('/', '\\')) && ! is_dir($sourcePath)) {
            mkdir($destPath, 0777, true);
            $destPath = $this->removeTrailingSlash($destPath);
        }

        // If source doesn't exist, check if it's a glob expression, otherwise we have nothing we can do
        if (!file_exists($sourcePath)) {
            // Handle globing
            $matches = glob($sourcePath);
            if ($matches) {
                foreach ($matches as $match) {
                    $newDest = substr($destPath . '/' . basename($match), strlen($this->getDestDir()));
                    $newDest = ltrim($newDest, ' \\/');
                    $this->create(substr($match, strlen($this->getSourceDir())+1), $newDest);
                }
                return true;
            }

            // Source file isn't a valid file or glob
            throw new \ErrorException("Source $sourcePath does not exist");
        }
        return $this->createDelegate($source, $dest);
    }

    /**
     * Remove (unlink) the destination file
     *
     * @param string $source
     * @param string $dest
     * @throws \ErrorException
     */
    public function remove($source, $dest)
    {
        $sourcePath = $this->getSourceDir() . '/' . $this->removeTrailingSlash($source);
        $destPath = $this->getDestDir() . '/' . $dest;

        // If source doesn't exist, check if it's a glob expression, otherwise we have nothing we can do
        if (!file_exists($sourcePath)) {
            // Handle globing
            $matches = glob($sourcePath);
            if ($matches) {
                foreach ($matches as $match) {
                    $newDest = substr($destPath . '/' . basename($match), strlen($this->getDestDir()));
                    $newDest = ltrim($newDest, ' \\/');
                    $this->remove(substr($match, strlen($this->getSourceDir())+1), $newDest);
                }
                return;
            }

            // Source file isn't a valid file or glob
            throw new \ErrorException("Source $sourcePath does not exist");
        }

        // MP Avoid removing whole folders in case the modman file is not 100% well-written
        // e.g. app/etc/modules/Testmodule.xml  app/etc/modules/ installs correctly, but would otherwise delete the whole app/etc/modules folder!
        if (basename($sourcePath) !== basename($destPath)) {
            $destPath .= '/' . basename($source);
        }
        self::rmdirRecursive($destPath);
    }

    /**
     * Remove an empty directory branch up to $stopDir, or stop at the first non-empty parent.
     *
     * @param string $dir
     * @param string $stopDir
     */
    public function rmEmptyDirsRecursive($dir, $stopDir = null)
    {
        $absoluteDir = $this->getDestDir() . '/' . $dir;
        if (is_dir($absoluteDir)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($absoluteDir),
                    \RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($iterator as $item) {
                $path = (string) $item;
                if (!strcmp($path, '.') || !strcmp($path, '..')) {
                    continue;
                }
                // The directory contains something, do not remove
                return;
            }
            // RecursiveIteratorIterator have opened handle on $absoluteDir
            // that cause Windows to block the directory and not remove it until
            // the iterator will be destroyed.
            unset($iterator);

            // The specified directory is empty
            if (@rmdir($absoluteDir)) {
                // If the parent directory doesn't match the $stopDir and it's empty, remove it, too
                $parentDir = dirname($dir);
                $absoluteParentDir = $this->getDestDir() . '/' . $parentDir;
                if (! isset($stopDir) || (realpath($stopDir) !== realpath($absoluteParentDir))) {
                    // Remove the parent directory if it is empty
                    $this->rmEmptyDirsRecursive($parentDir);
                }
            }
        }
    }

    /**
     * Recursively removes the specified directory or file
     *
     * @param $dir
     */
    public static function rmdirRecursive($dir)
    {
        $fs = new \Composer\Util\Filesystem();
        if(is_dir($dir)){
            $result = $fs->removeDirectory($dir);
        }else{
            @unlink($dir);
        }
        
        return;
    }


    /**
     * Create the module's files in the given destination.
     *
     * NOTE: source and dest have to be passed as relative directories, like they are listed in the mapping
     *
     * @param string $source
     * @param string $dest
     * @return bool
     */
    abstract protected function createDelegate($source, $dest);

}
