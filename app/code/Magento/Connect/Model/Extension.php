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
 * @category    Magento
 * @package     Magento_Connect
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Extension model
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Connect\Model;

class Extension extends \Magento\Object
{
    /**
     * Cache for targets
     *
     * @var array
     */
    protected $_targets;

    /**
     * Internal cache for package
     *
     * @var \Magento\Connect\Package
     */
    protected $_package;

    /**
     * @var \Magento\Filesystem $filesystem
     */
    protected $_filesystem;

    /**
     * Connect data
     *
     * @var \Magento\Connect\Helper\Data
     */
    protected $_connectData = null;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * Session
     *
     * @var \Magento\Connect\Model\Session
     */
    protected $_session;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Connect\Helper\Data $connectData
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Connect\Model\Session $session
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Connect\Helper\Data $connectData,
        \Magento\Filesystem $filesystem,
        \Magento\Connect\Model\Session $session,
        array $data = array()
    ) {
        $this->_coreData = $coreData;
        $this->_connectData = $connectData;
        $this->_session = $session;
        parent::__construct($data);
        $this->_filesystem = $filesystem;
    }

    /**
     * Return package object
     *
     * @return \Magento\Connect\Package
     */
    protected function getPackage()
    {
        if (!$this->_package instanceof \Magento\Connect\Package) {
            $this->_package = new \Magento\Connect\Package();
        }
        return $this->_package;
    }

    /**
     * Set package object.
     *
     * @return \Magento\Connect\Model\Extension
     */
    public function generatePackageXml()
    {
        $this->_session
            ->setLocalExtensionPackageFormData($this->getData());

        $this->_setPackage()
            ->_setRelease()
            ->_setAuthors()
            ->_setDependencies()
            ->_setContents();
        if (!$this->getPackage()->validate()) {
            $message = $this->getPackage()->getErrors();
            throw new \Magento\Core\Exception(__($message[0]));
        }
        $this->setPackageXml($this->getPackage()->getPackageXml());
        return $this;
    }

    /**
     * Set general information.
     *
     * @return \Magento\Connect\Model\Extension
     */
    protected function _setPackage()
    {
        $this->getPackage()
            ->setName($this->getData('name'))
            ->setChannel($this->getData('channel'))
            ->setLicense($this->getData('license'), $this->getData('license_uri'))
            ->setSummary($this->getData('summary'))
            ->setDescription($this->getData('description'));
        return $this;
    }

    /**
     * Set release information
     *
     * @return \Magento\Connect\Model\Extension
     */
    protected function _setRelease()
    {
        $this->getPackage()
            ->setDate(date('Y-m-d'))
            ->setTime(date('H:i:s'))
            ->setVersion($this->getData('version')?$this->getData('version'):$this->getData('release_version'))
            ->setStability($this->getData('stability'))
            ->setNotes($this->getData('notes'));
        return $this;
    }

    /**
     * Set authors
     *
     * @return \Magento\Connect\Model\Extension
     */
    protected function _setAuthors()
    {
        $authors = $this->getData('authors');
        foreach ($authors['name'] as $i => $name) {
            $user  = $authors['user'][$i];
            $email = $authors['email'][$i];
            $this->getPackage()->addAuthor($name, $user, $email);
        }
        return $this;
    }

    protected function packageFilesToArray($filesString)
    {
        $packageFiles = array();
        if ($filesString) {
            $filesArray = preg_split("/[\n\r]+/", $filesString);
            foreach ($filesArray as $file) {
                $file = trim($file, "/");
                $res = explode(DIRECTORY_SEPARATOR, $file, 2);
                array_map('trim', $res);
                if (2 == count($res)) {
                    $packageFiles[] = array('target'=>$res[0], 'path'=>$res[1]);
                }
            }
        }
        return $packageFiles;
    }

    /**
     * Set php, php extensions, another packages dependencies
     *
     * @return \Magento\Connect\Model\Extension
     */
    protected function _setDependencies()
    {
        $this->getPackage()
            ->clearDependencies()
            ->setDependencyPhpVersion($this->getData('depends_php_min'), $this->getData('depends_php_max'));

        foreach ($this->getData('depends') as $deptype=>$deps) {
            foreach ($deps['name'] as $i=>$type) {
                if (0===$i) {
                    continue;
                }
                $name = $deps['name'][$i];
                $min = !empty($deps['min'][$i]) ? $deps['min'][$i] : false;
                $max = !empty($deps['max'][$i]) ? $deps['max'][$i] : false;

                $files = !empty($deps['files'][$i]) ? $deps['files'][$i] : false;
                $packageFiles = $this->packageFilesToArray($files);

                if ($deptype !== 'extension') {
                    $channel = !empty($deps['channel'][$i])
                        ? $deps['channel'][$i]
                        : 'connect.magentocommerce.com/core';
                }
                switch ($deptype) {
                    case 'package':
                        $this->getPackage()->addDependencyPackage($name, $channel, $min, $max, $packageFiles);
                        break;

                    case 'extension':
                        $this->getPackage()->addDependencyExtension($name, $min, $max);
                        break;
                }
            }
        }
        return $this;
    }

    /**
     * Set contents. Add file or entire directory.
     *
     * @return \Magento\Connect\Model\Extension
     */
    protected function _setContents()
    {
        $this->getPackage()->clearContents();
        $contents = $this->getData('contents');
        foreach ($contents['target'] as $i=>$target) {
            if (0===$i) {
                continue;
            }
            switch ($contents['type'][$i]) {
                case 'file':
                    $this->getPackage()->addContent($contents['path'][$i], $contents['target'][$i]);
                    break;

                case 'dir':
                    $target = $contents['target'][$i];
                    $path = $contents['path'][$i];
                    $include = $contents['include'][$i];
                    $ignore = $contents['ignore'][$i];
                    $this->getPackage()->addContentDir($target, $path, $ignore, $include);
                    break;
            }
        }
        return $this;
    }

    /**
     * Save package file to var/connect.
     *
     * @return boolean
     */
    public function savePackage()
    {
        if ($this->getData('file_name') != '') {
            $fileName = $this->getData('file_name');
            $this->unsetData('file_name');
        } else {
            $fileName = $this->getName();
        }

        if (!preg_match('/^[a-z0-9]+[a-z0-9\-\_\.]*([\/\\\\]{1}[a-z0-9]+[a-z0-9\-\_\.]*)*$/i', $fileName)) {
            return false;
        }

        if (!$this->getPackageXml()) {
            $this->generatePackageXml();
        }

        if (!$this->getPackageXml()) {
            return false;
        }

        try {
            $path = $this->_connectData->getLocalPackagesPath();
            $this->_filesystem->write($path . 'package.xml', $this->getPackageXml());

            $this->unsPackageXml();
            $this->unsTargets();
            $xml = $this->_coreData->assocToXml($this->getData());
            $xml = new \Magento\Simplexml\Element($xml->asXML());

            // prepare dir to save
            $parts = explode(DS, $fileName);
            array_pop($parts);
            $newDir = implode(DS, $parts);

            if (!empty($newDir) && !$this->_filesystem->isDirectory($path . $newDir)) {
                $this->_filesystem->ensureDirectoryExists($path, $newDir, 0777);
            }
            $this->_filesystem->write($path . $fileName . '.xml', $xml->asNiceXml());
        } catch (\Magento\Filesystem\FilesystemException $e) {
            return false;
        }
        return true;
    }

    /**
     * Create package file
     *
     * @return boolean
     */
    public function createPackage()
    {
        $path = $this->_connectData->getLocalPackagesPath();
        if (!is_dir($path)) {
            if (!mkdir($path)) {
                return false;
            }
        }
        if (!$this->getPackageXml()) {
            $this->generatePackageXml();
        }
        $this->getPackage()->save($path);
        return true;
    }

    /**
     * Create package file compatible with previous version of Magento Connect Manager
     *
     * @return boolean
     */
    public function createPackageV1x()
    {
        $path = $this->_connectData->getLocalPackagesPathV1x();
        if (!is_dir($path)) {
            if (!mkdir($path)) {
                return false;
            }
        }
        if (!$this->getPackageXml()) {
            $this->generatePackageXml();
        }
        $this->getPackage()->saveV1x($path);
        return true;
    }

    /**
     * Retrieve targets
     *
     * @return array
     */
    public function getLabelTargets()
    {
        if (!is_array($this->_targets)) {
            $objectTarget = new \Magento\Connect\Package\Target();
            $this->_targets = $objectTarget->getLabelTargets();
        }
        return $this->_targets;
    }

}
