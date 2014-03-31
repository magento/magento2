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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Connect;

use Magento\Connect\Package\Target;

/**
 * Class to work with Magento Connect packages
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Package
{
    /*
     * Current version of magento connect package format
     */
    const PACKAGE_VERSION_2X = '2';

    /*
     * Previous version of magento connect package format
     */
    const PACKAGE_VERSION_1X = '1';

    /**
     * Contain \SimpleXMLElement for composing document.
     *
     * @var \SimpleXMLElement
     */
    protected $_packageXml;

    /**
     * Internal cache
     *
     * @var array
     */
    protected $_authors;

    /**
     * Internal cache
     *
     * @var array
     */
    protected $_contents;

    /**
     * Internal cache
     *
     * @var array
     */
    protected $_hashContents;

    /**
     * Internal cache
     *
     * @var array
     */
    protected $_compatible;

    /**
     * Internal cache
     *
     * @var array
     */
    protected $_dependencyPhpExtensions;

    /**
     * Internal cache
     *
     * @var array
     */
    protected $_dependencyPackages;

    /**
     * A helper object that can read from a package archive
     *
     * @var \Magento\Connect\Package\Reader
     */
    protected $_reader;

    /**
     * A helper object that can create and write to a package archive
     *
     * @var \Magento\Connect\Package\Writer
     */
    protected $_writer;

    /**
     * Validator object
     *
     * @var \Magento\Connect\Validator
     */
    protected $_validator = null;

    /**
     * Validation errors
     *
     * @var array
     */
    protected $_validationErrors = array();

    /**
     * Object with target
     *
     * @var Target
     */
    protected $_target = null;

    /**
     * General purpose Magento util
     *
     * @var \Magento\Util
     */
    protected $_util = null;

    /**
     * Creates a package object (empty, or from existing archive, or from package definition xml)
     *
     * @param null|string|resource $source
     * @param \Magento\Util|null $util
     * @throws \Magento\Exception
     */
    public function __construct($source = null, \Magento\Util $util = null)
    {
        $this->_util = $util ? $util : new \Magento\Util();
        libxml_use_internal_errors(true);

        if (is_string($source)) {
            // check what's in the string (a package definition or a package filename)
            if (0 === strpos($source, "<?xml")) {
                // package definition xml
                $this->_init($source);
            } elseif (is_file($source) && is_readable($source)) {
                // package archive filename
                $this->_loadFile($source);
            } else {
                throw new \Magento\Exception('Invalid package source');
            }
        } elseif (is_resource($source)) {
            $this->_loadResource($source);
        } elseif (is_null($source)) {
            $this->_init();
        } else {
            throw new \Magento\Exception('Invalid package source');
        }
    }

    /**
     * Initializes an empty package object
     *
     * @param null|string $definition optional package definition xml
     * @return $this
     */
    protected function _init($definition = null)
    {

        if (!is_null($definition)) {
            $this->_packageXml = simplexml_load_string($definition);
        } else {
            $packageXmlStub = <<<END
<?xml version="1.0"?>
<package>
    <name />
    <version />
    <stability />
    <license />
    <channel />
    <extends />
    <summary />
    <description />
    <notes />
    <authors />
    <date />
    <time />
    <contents />
    <compatible />
    <dependencies />
</package>
END;
            $this->_packageXml = simplexml_load_string($packageXmlStub);
        }
        return $this;
    }

    /**
     * Loads a package from specified file
     *
     * @param string $filename
     * @return $this
     */
    protected function _loadFile($filename = '')
    {
        if (is_null($this->_reader)) {
            $this->_reader = new \Magento\Connect\Package\Reader($filename);
        }
        $content = $this->_reader->load();
        $this->_packageXml = simplexml_load_string($content);
        return $this;
    }

    /**
     * Creates a package and saves it
     *
     * @param string $path
     * @return $this
     */
    public function save($path)
    {
        $this->validate();
        $path = rtrim($path, "\\/") . '/';
        $this->_savePackage($path);
        return $this;
    }

    /**
     * Creates a package that is compatible with the previous version of Magento Connect Manager and saves it
     *
     * @param string $path
     * @return $this
     */
    public function saveV1x($path)
    {
        $this->validate();
        $path = rtrim($path, "\\/") . '/';
        $this->_savePackageV1x($path);
        return $this;
    }

    /**
     * Creates a package archive and saves it to specified path
     *
     * @param string $path
     * @return $this
     */
    protected function _savePackage($path)
    {
        $fileName = $this->getReleaseFilename();
        if (is_null($this->_writer)) {
            $this->_writer = new \Magento\Connect\Package\Writer($this->getContents(), $path . $fileName);
        }
        $this->_writer->composePackage()->addPackageXml($this->getPackageXml())->archivePackage();
        return $this;
    }

    /**
     * Creates a package archive and saves it to specified path
     * Package is compatible with the previous version of magento Connect Manager
     *
     * @param string $path
     * @return $this
     */
    protected function _savePackageV1x($path)
    {
        $fileName = $this->getReleaseFilename();
        $writer = new \Magento\Connect\Package\Writer($this->getContents(), $path . $fileName);
        $writer->composePackageV1x(
            $this->getContentsV1x()
        )->addPackageXml(
            $this->_getPackageXmlV1x()
        )->archivePackage();
        return $this;
    }

    /**
     * Generate package xml that is compatible with first version of Magento Connect Manager
     * Function uses already generated package xml to import data
     *
     * @return string
     */
    protected function _getPackageXmlV1x()
    {
        $newPackageXml = $this->_packageXml;
        $packageXmlV1xStub = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<package packagerversion="1.9.1"
version="2.0"
xmlns="http://pear.php.net/dtd/package-2.0"
xmlns:tasks="http://pear.php.net/dtd/tasks-1.0"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd" />
END;
        $packageXmlV1x = simplexml_load_string($packageXmlV1xStub);
        // Note: The previous version of MCM requires precise node order in package.xml file
        $packageXmlV1x->addChild('name', (string)$newPackageXml->name);
        $packageXmlV1x->addChild('channel', $this->_convertChannelToV1x((string)$newPackageXml->channel));
        $packageXmlV1x->addChild('summary', (string)$newPackageXml->summary);
        $packageXmlV1x->addChild('description', (string)$newPackageXml->description);
        // Import authors
        foreach ($newPackageXml->authors->author as $author) {
            $leadNode = $packageXmlV1x->addChild('lead');
            $leadNode->addChild('name', (string)$author->name);
            $leadNode->addChild('user', (string)$author->user);
            $leadNode->addChild('email', (string)$author->email);
            $leadNode->addChild('active', 'yes');
        }
        // Import date and time
        $packageXmlV1x->addChild('date', (string)$newPackageXml->date);
        $packageXmlV1x->addChild('time', (string)$newPackageXml->time);
        // Import version
        $versionNode = $packageXmlV1x->addChild('version');
        $versionNode->addChild('release', (string)$newPackageXml->version);
        $versionNode->addChild('api', (string)$newPackageXml->version);
        // Import stability
        $stabilityNode = $packageXmlV1x->addChild('stability');
        $stabilityNode->addChild('release', (string)$newPackageXml->stability);
        $stabilityNode->addChild('api', (string)$newPackageXml->stability);
        // Import license
        $licenseNode = $packageXmlV1x->addChild('license', (string)$newPackageXml->license);
        if ($newPackageXml->license['uri']) {
            $licenseNode->addAttribute('uri', (string)$newPackageXml->license['uri']);
        }
        $packageXmlV1x->addChild('notes', (string)$newPackageXml->notes);
        // Import content
        $conentsRootDirNode = $packageXmlV1x->addChild('contents')->addChild('dir');
        $conentsRootDirNode->addAttribute('name', '/');
        foreach ($newPackageXml->contents->target as $target) {
            $role = (string)$target['name'];
            $this->_mergeContentsToV1x($conentsRootDirNode, $target, $role);
        }
        // Import dependencies
        $requiredDependenciesNode = $packageXmlV1x->addChild('dependencies')->addChild('required');
        $requiredDependenciesPhpNode = $requiredDependenciesNode->addChild('php');
        $requiredDependenciesPhpNode->addChild('min', (string)$newPackageXml->dependencies->required->php->min);
        $requiredDependenciesPhpNode->addChild('max', (string)$newPackageXml->dependencies->required->php->max);
        $requiredDependenciesNode->addChild('pearinstaller')->addChild('min', '1.6.2');
        // Handle packages
        foreach ($newPackageXml->dependencies->required->package as $package) {
            $packageNode = $requiredDependenciesNode->addChild('package');
            $packageNode->addChild('name', (string)$package->name);
            // Convert channel to previous version format
            $channel = (string)$package->channel;
            $channel = $this->_convertChannelToV1x($channel);
            $packageNode->addChild('channel', $channel);
            $minVersion = (string)$package->min;
            if ($minVersion) {
                $packageNode->addChild('min', $minVersion);
            }
            $maxVersion = (string)$package->max;
            if ($maxVersion) {
                $packageNode->addChild('max', $maxVersion);
            }
        }
        // Handle extensions
        foreach ($newPackageXml->dependencies->required->extension as $extension) {
            $extensionNode = $requiredDependenciesNode->addChild('extension');
            $extensionNode->addChild('name', (string)$extension->name);
            $minVersion = (string)$extension->min;
            if ($minVersion) {
                $extensionNode->addChild('min', $minVersion);
            }
            $maxVersion = (string)$extension->max;
            if ($maxVersion) {
                $extensionNode->addChild('max', $maxVersion);
            }
        }
        $packageXmlV1x->addChild('phprelease');

        return $packageXmlV1x->asXML();
    }

    /**
     * Merge contents of source element into destination element
     * Function converts <file/> and <dir/> nodes into format that is compatible
     * with previous version of Magento Connect Manager
     *
     * @param \SimpleXMLElement $destination
     * @param \SimpleXMLElement $source
     * @param string $role
     * @return $this
     */
    protected function _mergeContentsToV1x($destination, $source, $role)
    {
        foreach ($source->children() as $child) {
            if ($child->getName() == 'dir') {
                $newDestination = $destination;
                if ($child['name'] != '.') {
                    $directoryElement = $destination->addChild('dir');
                    $directoryElement->addAttribute('name', $child['name']);
                    $newDestination = $directoryElement;
                }
                $this->_mergeContentsToV1x($newDestination, $child, $role);
            } elseif ($child->getName() == 'file') {
                $fileElement = $destination->addChild('file');
                $fileElement->addAttribute('name', $child['name']);
                $fileElement->addAttribute('md5sum', $child['hash']);
                $fileElement->addAttribute('role', $role);
            }
        }
        return $this;
    }

    /**
     * Retrieve Target object
     *
     * @return Target
     */
    protected function getTarget()
    {
        if (!$this->_target instanceof Target) {
            $this->_target = new Target();
        }
        return $this->_target;
    }

    /**
     * @param Target $arg
     * @return void
     */
    public function setTarget($arg)
    {
        if ($arg instanceof Target) {
            $this->_target = $arg;
        }
    }

    /* Mutators */

    /**
     * Puts value to name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->_packageXml->name = $name;
        return $this;
    }

    /**
     * Puts value to <channel />
     *
     * @param string $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->_packageXml->channel = $channel;
        return $this;
    }

    /**
     * Puts value to <summary />
     *
     * @param string $summary
     * @return $this
     */
    public function setSummary($summary)
    {
        $this->_packageXml->summary = $summary;
        return $this;
    }

    /**
     * Puts value to <description />
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->_packageXml->description = $description;
        return $this;
    }

    /**
     * Puts value to <authors />
     *
     * The format of the authors array is
     * array(
     *     array('name'=>'Name1', 'user'=>'User1', 'email'=>'email1@email.com'),
     *     array('name'=>'Name2', 'user'=>'User2', 'email'=>'email2@email.com'),
     * );
     *
     * @param array $authors
     * @return $this
     */
    public function setAuthors($authors)
    {
        $this->_authors = $authors;
        foreach ($authors as $_author) {
            $this->addAuthor($_author['name'], $_author['user'], $_author['email']);
        }
        return $this;
    }

    /**
     * Add author to <authors/>
     *
     * @param string $name
     * @param string $user
     * @param string $email
     * @return $this
     */
    public function addAuthor($name = null, $user = null, $email = null)
    {
        $this->_authors[] = array('name' => $name, 'user' => $user, 'email' => $email);
        $author = $this->_packageXml->authors->addChild('author');
        $author->addChild('name', $name);
        $author->addChild('user', $user);
        $author->addChild('email', $email);
        return $this;
    }

    /**
     * Puts value to <date/>. Format should be Y-M-D.
     *
     * @param string $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->_packageXml->date = $date;
        return $this;
    }

    /**
     * Puts value to <time />. Format should be H:i:s.
     *
     * @param string $time
     * @return $this
     */
    public function setTime($time)
    {
        $this->_packageXml->time = $time;
        return $this;
    }

    /**
     * Puts value to <version/>. Format should be X.Y.Z.
     *
     * @param string $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->_packageXml->version = $version;
        return $this;
    }

    /**
     * Puts value to <stability/>. It can be alpha, beta, devel and stable.
     *
     * @param string $stability
     * @return $this
     */
    public function setStability($stability)
    {
        $this->_packageXml->stability = $stability;
        return $this;
    }

    /**
     * Puts value to <license/>, also method can used for set attribute URI.
     *
     * @param string $license
     * @param string $uri
     * @return $this
     */
    public function setLicense($license, $uri = null)
    {
        $this->_packageXml->license = $license;
        if ($uri) {
            $this->_packageXml->license['uri'] = $uri;
        }
        return $this;
    }

    /**
     * Puts value to <notes/>.
     *
     * @param string $notes
     * @return $this
     */
    public function setNotes($notes)
    {
        $this->_packageXml->notes = $notes;
        return $this;
    }

    /**
     * Retrieve \SimpleXMLElement node by xpath. If it absent, create new.
     * For comparing nodes method uses attribute "name" in each nodes.
     * If attribute "name" is same for both nodes, nodes are same.
     *
     * @param string $tag
     * @param \SimpleXMLElement $parent
     * @param string $name
     * @return \SimpleXMLElement
     */
    protected function _getNode($tag, $parent, $name = '')
    {
        $found = false;
        foreach ($parent->xpath($tag) as $_node) {
            if ($_node['name'] == $name) {
                $node = $_node;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $node = $parent->addChild($tag);
            if ($name) {
                $node->addAttribute('name', $name);
            }
        }
        return $node;
    }

    /**
     * Add directory or file to <contents />.
     *
     * @param string $path Path to directory or file
     * @param string $targetName Target name.
     * @return $this
     */
    public function addContent($path, $targetName)
    {
        $found = false;
        $parent = $this->_getNode('target', $this->_packageXml->contents, $targetName);
        $source = str_replace('\\', '/', $path);
        $directories = explode('/', dirname($source));
        foreach ($directories as $directory) {
            $parent = $this->_getNode('dir', $parent, $directory);
        }
        $fileName = basename($source);
        if ($fileName != '') {
            $fileNode = $parent->addChild('file');
            $fileNode->addAttribute('name', $fileName);
            $targetDir = $this->getTarget()->getTargetUri($targetName);
            $hash = md5_file($targetDir . '/' . $path);
            $fileNode->addAttribute('hash', $hash);
        }
        return $this;
    }

    /**
     * Add directory recursively (with subdirectory and file).
     * Exclude and Include can be add using Regular Expression.
     *
     * @param string $targetName Target name
     * @param string $path Path to directory
     * @param string $exclude Exclude
     * @param string $include Include
     * @return $this
     */
    public function addContentDir($targetName, $path, $exclude = null, $include = null)
    {
        $targetDir = $this->getTarget()->getTargetUri($targetName);
        $targetDirLen = strlen($targetDir . '/');
        //get all subdirectories and files.
        $entries = @glob($targetDir . '/' . $path . '/' . "{,.}*", GLOB_BRACE);
        if (!empty($entries)) {
            foreach ($entries as $entry) {
                $filePath = substr($entry, $targetDirLen);
                // TODO: Check directory before includes/excludes
                if (is_dir($entry)) {
                    $baseName = basename($entry);
                    if (in_array($baseName, array('.', '..', '.svn'))) {
                        continue;
                    }
                    //for subdirectory call method recursively
                    $this->addContentDir($targetName, $filePath, $exclude, $include);
                    continue;
                }
                if (!empty($include) && !preg_match($include, $filePath)) {
                    continue;
                }
                if (!empty($exclude) && preg_match($exclude, $filePath)) {
                    continue;
                }

                if (is_file($entry)) {
                    $this->addContent($filePath, $targetName);
                }
            }
        }
        return $this;
    }

    /**
     * Add value to <compatible />.
     *
     * @param string $packageName
     * @param string $channel
     * @param string $minVersion
     * @param string $maxVersion
     * @return $this
     */
    public function addCompatible($packageName, $channel, $minVersion, $maxVersion)
    {
        $package = $this->_packageXml->compatible->addChild('package');
        $package->addChild('name', $packageName);
        $package->addChild('channel', $channel);
        $package->addChild('min', $minVersion);
        $package->addChild('max', $maxVersion);
        return $this;
    }

    /**
     * Set dependency from php version.
     *
     * @param string $minVersion
     * @param string $maxVersion
     * @return $this
     */
    public function setDependencyPhpVersion($minVersion, $maxVersion)
    {
        $parent = $this->_packageXml->dependencies;
        $parent = $this->_getNode('required', $parent);
        $parent = $this->_getNode('php', $parent);
        $parent->addChild('min', $minVersion);
        $parent->addChild('max', $maxVersion);
        return $this;
    }

    /**
     * Check PHP version restriction
     * @return true|string
     */
    public function checkPhpVersion()
    {
        $min = $this->getDependencyPhpVersionMin();
        $max = $this->getDependencyPhpVersionMax();

        $minOk = $min ? version_compare(PHP_VERSION, $min, ">=") : true;
        $maxOk = $max ? version_compare($this->_util->getTrimmedPhpVersion(), $max, "<=") : true;

        if (!$minOk || !$maxOk) {
            $err = "requires PHP version ";
            if ($min && $max) {
                $err .= " >= {$min} and <= {$max} ";
            } elseif ($min) {
                $err .= " >= {$min} ";
            } elseif ($max) {
                $err .= " <= {$max} ";
            }
            $err .= " current is: " . PHP_VERSION;
            return $err;
        }
        return true;
    }

    /**
     * Check PHP extensions availability
     * @throws \Exception On failure
     * @return true|array
     */
    public function checkPhpDependencies()
    {
        $errors = array();
        foreach ($this->getDependencyPhpExtensions() as $dep) {
            if (!extension_loaded($dep['name'])) {
                $errors[] = $dep;
            }
        }
        if (count($errors)) {
            return $errors;
        }
        return true;
    }

    /**
     * Set dependency from php extensions.
     *
     * The $extension has next view:
     * array('curl', 'mysql')
     *
     * @param array|string $extensions
     * @return $this
     */
    public function setDependencyPhpExtensions($extensions)
    {
        foreach ($extensions as $_extension) {
            $this->addDependencyExtension($_extension['name'], $_extension['min_version'], $_extension['max_version']);
        }
        return $this;
    }

    /**
     * Set dependency from another packages.
     *
     * The $packages should contain:
     * array(
     *     array('name'=>'test1', 'channel'=>'test1', 'min_version'=>'0.0.1', 'max_version'=>'0.1.0'),
     *     array('name'=>'test2', 'channel'=>'test2', 'min_version'=>'0.0.1', 'max_version'=>'0.1.0'),
     * )
     *
     * @param array $packages
     * @param bool $clear
     * @return $this
     */
    public function setDependencyPackages($packages, $clear = false)
    {
        if ($clear) {
            unset($this->_packageXml->dependencies->required->package);
        }

        foreach ($packages as $_package) {

            $filesArrayCondition = isset($_package['files']) && is_array($_package['files']);
            $filesArray = $filesArrayCondition ? $_package['files'] : array();

            $this->addDependencyPackage(
                $_package['name'],
                $_package['channel'],
                $_package['min_version'],
                $_package['max_version'],
                $filesArray
            );
        }
        return $this;
    }

    /**
     * Add package to dependency packages.
     *
     * @param string $name
     * @param string $channel
     * @param string $minVersion
     * @param string $maxVersion
     * @param array $files
     * @return $this
     */
    public function addDependencyPackage($name, $channel, $minVersion, $maxVersion, $files = array())
    {
        $parent = $this->_packageXml->dependencies;
        $parent = $this->_getNode('required', $parent);
        $parent = $parent->addChild('package');
        $parent->addChild('name', $name);
        $parent->addChild('channel', $channel);
        $parent->addChild('min', $minVersion);
        $parent->addChild('max', $maxVersion);
        if (count($files)) {
            $parent = $parent->addChild('files');
            foreach ($files as $row) {
                if (!empty($row['target']) && !empty($row['path'])) {
                    $node = $parent->addChild("file");
                    $node["target"] = $row['target'];
                    $node["path"] = $row['path'];
                }
            }
        }
        return $this;
    }

    /**
     * Add package to dependency extension.
     *
     * @param string $name
     * @param string $minVersion
     * @param string $maxVersion
     * @return $this
     */
    public function addDependencyExtension($name, $minVersion, $maxVersion)
    {
        $parent = $this->_packageXml->dependencies;
        $parent = $this->_getNode('required', $parent);
        $parent = $parent->addChild('extension');
        $parent->addChild('name', $name);
        $parent->addChild('min', $minVersion);
        $parent->addChild('max', $maxVersion);
        return $this;
    }

    /* Accessors */

    /**
     * Getter
     *
     * @return string
     */
    public function getName()
    {
        return (string)$this->_packageXml->name;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getChannel()
    {
        return (string)$this->_packageXml->channel;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getSummary()
    {
        return (string)$this->_packageXml->summary;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getDescription()
    {
        return (string)$this->_packageXml->description;
    }

    /**
     * Get list of authors in associative array.
     *
     * @return array
     */
    public function getAuthors()
    {
        if (is_array($this->_authors)) {
            return $this->_authors;
        }
        $this->_authors = array();
        if (!isset($this->_packageXml->authors->author)) {
            return array();
        }
        foreach ($this->_packageXml->authors->author as $_author) {
            $this->_authors[] = array(
                'name' => (string)$_author->name,
                'user' => (string)$_author->user,
                'email' => (string)$_author->email
            );
        }
        return $this->_authors;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getDate()
    {
        return (string)$this->_packageXml->date;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getTime()
    {
        return (string)$this->_packageXml->time;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getVersion()
    {
        return (string)$this->_packageXml->version;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getStability()
    {
        return (string)$this->_packageXml->stability;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getLicense()
    {
        return (string)$this->_packageXml->license;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getLicenseUri()
    {
        return (string)$this->_packageXml->license['uri'];
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getNotes()
    {
        return (string)$this->_packageXml->notes;
    }

    /**
     * Create list of all files from package.xml
     *
     * @return array
     */
    public function getContents()
    {
        if (is_array($this->_contents)) {
            return $this->_contents;
        }
        $this->_contents = array();
        if (!isset($this->_packageXml->contents->target)) {
            return $this->_contents;
        }
        foreach ($this->_packageXml->contents->target as $target) {
            $targetUri = $this->getTarget()->getTargetUri($target['name']);
            $this->_getList($target, $targetUri);
        }
        return $this->_contents;
    }

    /**
     * Create list of all files from package.xml compatible with previous version of Magento Connect Manager
     *
     * @return array
     */
    public function getContentsV1x()
    {
        $currentContents = $this->_contents;
        $this->_contents = array();

        if (!isset($this->_packageXml->contents->target)) {
            return $this->_contents;
        }
        foreach ($this->_packageXml->contents->target as $target) {
            $this->_getList($target, '');
        }
        $contents = $this->_contents;

        $this->_contents = $currentContents;
        return $contents;
    }

    /**
     * Helper for getContents(). Create recursively list.
     *
     * @param \SimpleXMLElement $parent
     * @param string $path
     * @return void
     */
    protected function _getList($parent, $path)
    {
        if (count($parent) == 0) {
            $this->_contents[] = $path;
        } else {
            foreach ($parent as $_content) {
                $this->_getList($_content, ($path ? $path . '/' : '') . $_content['name']);
            }
        }
    }

    /**
     * Create list of all files from package.xml with hash
     *
     * @return array
     */
    public function getHashContents()
    {
        if (is_array($this->_hashContents)) {
            return $this->_hashContents;
        }
        $this->_hashContents = array();
        if (!isset($this->_packageXml->contents->target)) {
            return $this->_hashContents;
        }
        foreach ($this->_packageXml->contents->target as $target) {
            $targetUri = $this->getTarget()->getTargetUri($target['name']);
            $this->_getHashList($target, $targetUri);
        }
        return $this->_hashContents;
    }

    /**
     * Helper for getHashContents(). Create recursively list.
     *
     * @param \SimpleXMLElement $parent
     * @param string $path
     * @param string $hash
     * @return void
     */
    protected function _getHashList($parent, $path, $hash = '')
    {
        if (count($parent) == 0) {
            $this->_hashContents[$path] = $hash;
        } else {
            foreach ($parent as $_content) {
                $contentHash = '';
                if (isset($_content['hash'])) {
                    $contentHash = (string)$_content['hash'];
                }
                $this->_getHashList($_content, ($path ? $path . '/' : '') . $_content['name'], $contentHash);
            }
        }
    }

    /**
     * Get compatible packages.
     *
     * @return array
     */
    public function getCompatible()
    {
        if (is_array($this->_compatible)) {
            return $this->_compatible;
        }
        $this->_compatible = array();
        if (!isset($this->_packageXml->compatible->package)) {
            return array();
        }
        foreach ($this->_packageXml->compatible->package as $_package) {
            $this->_compatible[] = array(
                'name' => (string)$_package->name,
                'channel' => (string)$_package->channel,
                'min' => (string)$_package->min,
                'max' => (string)$_package->max
            );
        }
        return $this->_compatible;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getDependencyPhpVersionMin()
    {
        if (!isset($this->_packageXml->dependencies->required->php->min)) {
            return false;
        }
        return (string)$this->_packageXml->dependencies->required->php->min;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getDependencyPhpVersionMax()
    {
        if (!isset($this->_packageXml->dependencies->required->php->max)) {
            return false;
        }
        return (string)$this->_packageXml->dependencies->required->php->max;
    }

    /**
     * Get list of php extensions.
     *
     * @return array
     */
    public function getDependencyPhpExtensions()
    {
        if (is_array($this->_dependencyPhpExtensions)) {
            return $this->_dependencyPhpExtensions;
        }
        $this->_dependencyPhpExtensions = array();
        foreach ($this->_packageXml->dependencies->required->extension as $_package) {
            $this->_dependencyPhpExtensions[] = array(
                'name' => (string)$_package->name,
                'min' => (string)$_package->min,
                'max' => (string)$_package->max
            );
        }
        return $this->_dependencyPhpExtensions;
    }

    /**
     * Get list of dependency packages.
     *
     * @return array
     */
    public function getDependencyPackages()
    {
        $this->_dependencyPackages = array();
        foreach ($this->_packageXml->dependencies->required->package as $_package) {
            $add = array(
                'name' => (string)$_package->name,
                'channel' => (string)$_package->channel,
                'min' => (string)$_package->min,
                'max' => (string)$_package->max
            );
            if (isset($_package->files)) {
                $add['files'] = array();
                foreach ($_package->files as $node) {
                    if (isset($node->file)) {

                        $add['files'][] = array(
                            'target' => (string)$node->file['target'],
                            'path' => (string)$node->file['path']
                        );
                    }
                }
            }
            $this->_dependencyPackages[] = $add;
        }
        return $this->_dependencyPackages;
    }

    /**
     * Get string with XML content.
     *
     * @return string
     */
    public function getPackageXml()
    {
        return $this->_packageXml->asXml();
    }

    /**
     * Validator instance (single)
     *
     *  @return \Magento\Connect\Validator
     */
    protected function validator()
    {
        if (is_null($this->_validator)) {
            $this->_validator = new \Magento\Connect\Validator();
        }
        return $this->_validator;
    }

    /**
     * Get validation error strings
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_validationErrors;
    }

    /**
     * Setter for validation errors
     *
     * @param array $errors
     * @return void
     */
    protected function setErrors(array $errors)
    {
        $this->_validationErrors = $errors;
    }

    /**
     * Check validation result.
     * Returns true if package data is invalid.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return count($this->_validationErrors) != 0;
    }

    /**
     * Validate package. Errors can be
     * retrieved by calling getErrors();
     *
     * @return bool
     * @throws \Magento\Exception
     */
    public function validate()
    {
        $v = $this->validator();

        /**
         * Validation map
         *
         * Format:
         *
         * 'key' =>  array(
         *    'method' => this class method name to call, string, required
         *    'method_args' => optional args for 'method' call, array, optional
         *    'v_method' => validator method to call, string, required
         *    'error' => custom error string when validation fails, optional
         *               if not set, error string fprmatted as "Invalid '$key' specified"
         *    'v_error_method' => validator method - when called returned error string
         *                        prepared by validator, optional,
         *                        if not set => see 'error'
         *    'optional' => optional value, if it's empty validation result ignored
         *
         */
        $validateMap = array(
            'name' => array(
                'method' => 'getName',
                'v_method' => 'validatePackageName',
                'error' => "Invalid package name, allowed: [a-zA-Z0-9_-] chars"
            ),
            'version' => array(
                'method' => 'getVersion',
                'v_method' => 'validateVersion',
                'error' => "Invalid version, should be like: x.x.x"
            ),
            'stability' => array(
                'method' => 'getStability',
                'v_method' => 'validateStability',
                'error' => "Invalid stability"
            ),
            'date' => array(
                'method' => 'getDate',
                'v_method' => 'validateDate',
                'error' => "Invalid date, should be YYYY-DD-MM"
            ),
            'license_uri' => array(
                'method' => 'getLicenseUri',
                'v_method' => 'validateLicenseUrl',
                'error' => "Invalid license URL"
            ),
            'channel' => array(
                'method' => 'getChannel',
                'v_method' => 'validateChannelNameOrUri',
                'error' => "Invalid channel URL"
            ),
            'authors' => array(
                'method' => 'getAuthors',
                'v_method' => 'validateAuthors',
                'v_error_method' => 'getErrors'
            ),
            'php_min' => array(
                'method' => 'getDependencyPhpVersionMin',
                'v_method' => 'validateVersion',
                'error' => 'PHP minimum version invalid',
                'optional' => true
            ),
            'php_max' => array(
                'method' => 'getDependencyPhpVersionMax',
                'v_method' => 'validateVersion',
                'error' => 'PHP maximum version invalid',
                'optional' => true
            ),
            'compatible' => array(
                'method' => 'getCompatible',
                'v_method' => 'validateCompatible',
                'v_error_method' => 'getErrors'
            )
        );

        $errors = array();
        /**
         * Iterate validation map
         */
        foreach ($validateMap as $name => $data) {

            /**
             * Check mandatory rules fields
             */
            if (!isset($data['method'], $data['v_method'])) {
                throw new \Magento\Exception("Invalid rules specified!");
            }

            $method = $data['method'];
            $validatorMethod = $data['v_method'];

            /**
             * If $optional === false, value is mandatory
             */
            $optional = isset($data['optional']) ? (bool)$data['optional'] : false;

            /**
             * Check for method availability, package
             */
            if (!method_exists($this, $method)) {
                throw new \Magento\Exception("Invalid method specified for Package : {$method}");
            }

            /**
             * Check for method availability, validator
             */
            if (!method_exists($v, $validatorMethod)) {
                throw new \Magento\Exception("Invalid method specified for Validator : {$validatorMethod}");
            }

            /**
             * If    $data['error'] => get error string from $data['error']
             * Else  concatenate "Invalid '{$name}' specified"
             */
            $errorString = isset($data['error']) ? $data['error'] : "Invalid '{$name}' specified";

            /**
             * Additional method args check
             * array() by default
             */
            $methodArgs = isset($data['method_args']) ? $data['method_args'] : array();

            /**
             * Call package method
             */
            $out = @call_user_func_array(array($this, $method), $methodArgs);

            /**
             * Skip if result is empty and value is optional
             */
            if (empty($out) && $optional) {
                continue;
            }

            /**
             * Additional validator arguments, merged with array($out)
             */
            $validatorArgs = isset($data['v_args']) ? array_merge(array($out), $data['v_args']) : array($out);

            /**
             * Get validation result
             */
            $result = call_user_func_array(array($v, $validatorMethod), $validatorArgs);

            /**
             * Skip if validation success
             */
            if ($result) {
                continue;
            }

            /**
             * From where to get error string?
             * If    validator callback method specified, call it to get errors array
             * Else  get it from $errorString - local error string
             */
            $validatorFetchErrorsMethod = isset($data['v_error_method']) ? $data['v_error_method'] : false;
            if (false !== $validatorFetchErrorsMethod) {
                $errorString = call_user_func_array(array($v, $validatorFetchErrorsMethod), array());
            }

            /**
             * If   errors is array => merge
             * Else append
             */
            if (is_array($errorString)) {
                $errors = array_merge($errors, $errorString);
            } else {
                $errors[] = $errorString;
            }
        }
        /**
         * Set local errors
         */
        $this->setErrors($errors);
        /**
         * Return true if there's no errors :)
         */
        return !$this->hasErrors();
    }

    /**
     * Return package release filename w/o extension
     * @return string
     */
    public function getReleaseFilename()
    {
        return $this->getName() . "-" . $this->getVersion();
    }

    /**
     * Return release filepath w/o extension
     * @return string
     */
    public function getRelaseDirFilename()
    {
        return $this->getName() . '/' . $this->getVersion() . '/' . $this->getReleaseFilename();
    }

    /**
     * Clear dependencies
     *
     * @return $this
     */
    public function clearDependencies()
    {
        $this->_packageXml->dependencies = null;
        return $this;
    }

    /**
     * Clear contents
     *
     * @return $this
     */
    public function clearContents()
    {
        $this->_packageXml->contents = null;
        return $this;
    }

    /**
     * Convert package channel in order for it to be compatible with previous version of Magento Connect Manager
     *
     * @param string $channel
     * @return string
     */
    protected function _convertChannelToV1x($channel)
    {
        $channelMap = array('community' => 'connect.magentocommerce.com/community');
        if (isset($channelMap[$channel])) {
            $channel = $channelMap[$channel];
        }
        return $channel;
    }
}
