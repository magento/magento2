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
 * Class to manipulate with channel/package cache file
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Connect;

class Singleconfig
{

    /**
     * Cache data
     * @var array
     */
    protected $_data = array();

    /**
     * Filename
     * @var string
     */
    protected $_readFilename = false;

    /**
     *
     * @var unknown_type
     */
    protected $_debug = false;

    /**
     *
     * @var unknown_type
     */
    protected $_validator;

    /**
     * Internal keys constants
     */
    const K_CHAN = 'channels_by_name';
    const K_CHAN_URI = 'channels_by_uri';
    const K_CHAN_ALIAS = 'channel_aliases';
    const K_PACK = 'packages';
    const K_URI = 'uri';
    const K_CHAN_DATA = 'channel_data';
    const K_NAME = 'name';
    const K_VER = 'version';
    const K_STATE = 'stability';
    const K_XML = 'xml';
    const K_DEPS = 'deps';
    const K_PACK_DEPS = 'pack_deps';
    const K_CONFIG = 'config';

    public function getValidUri($str)
    {
        $data = parse_url($str);
        if(isset($data['path'])) {
            return $data['path'];
        }
        return false;
    }

    public function getFilename()
    {
        return $this->_readFilename;
    }

    public function formatUri($uri)
    {        
        $uri = rtrim($uri, "/");
        $uri = str_replace("http://", '', $uri);
        $uri = str_replace("ftp://", '', $uri);
        return $uri;
    }

    /**
     * Get data
     * @return unknown_type
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Constructor
     * @param srting $file
     * @return void
     */
    public function __construct($file = "cache.cfg")
    {
        $this->setEmptyConfig();
        if($file) {
            $this->_readFilename = $file;
            $this->load();
        }
    }


    /**
     * Load cache from file
     * @param string $file
     * @return void
     */
    public function load($file = false)
    {
        if(false === $file) {
            $file = $this->_readFilename;
        }
        if(false === $file) {
            return;
        }

        if(!file_exists($file)) {
            $this->save($file);
            return;
        }

        if(!is_readable($file)) {
            return $this->doError("File is not readable: '{$file}'");
        }

        $this->_readFilename = $file;

        $data = @file_get_contents($file);
        if(false === $data) {
            return $this->doError("Cannot get file contents: '{$file}'");
        }

        if(!$this->_debug) {
            $data = @gzuncompress($data);
            if(false === $data) {
                return $this->doError("Cannot unpack gzipped data in file contents: '{$file}'");
            }
        }
        $data = @unserialize($data);
        if(unserialize(false) === $data) {
            return $this->doError("Cannot unserialize data in file contents: '{$file}'");
        }


        $validData = true;
        foreach(array_keys($this->_data) as $k) {
            if(!isset($data[$k])) {
                $validData = false;
            } else {
                $this->_data[$k] = $data[$k];
            }
        }
        if($validData) {
            $this->_data = $data;
        } else {
            $this->save();
        }
    }

    /**
     * Save contents
     * @param string $file
     * @return void
     */
    public function save($file = false)
    {
        if(false === $file) {
            $file = $this->_readFilename;
        }
        if(false === $file) {
            return;
        }
        $data = @serialize($this->_data);
        if(!$this->_debug) {
            $data = @gzcompress($data);
        }
        $res = @file_put_contents($file, $data);
        if(!$res) {
            $this->doError("Cannot save: '{$file}'");
        }
    }

    /**
     * Set empty config skeleton
     * @return void
     */
    public function setEmptyConfig()
    {
        $this->_data = array(
        self::K_CHAN => array (),
        self::K_CHAN_URI => array (),
        self::K_CHAN_ALIAS => array (),
        );
    }


    public function isChannel($chanName)
    {
        if($this->isChannelName($chanName)) {
            return true;
        }
        if($this->isChannelUri($chanName)) {
            return true;
        }
        if($this->isChannelAlias($chanName)) {
            return true;
        }
        return false;
    }

    /**
     * Get channel
     * @param string $chanName
     * @return array
     */
    public function getChannel($chanName)
    {
        if($this->isChannelAlias($chanName)) {
            $chanName = $this->getChannelNameByAlias($chanName);
        } elseif($this->isChannelUri($chanName)) {
            $chanName = $this->getChannelUriRecord($chanName);
        }
        if($this->isChannelName($chanName)) {
            return $this->_data[self::K_CHAN][$chanName];
        }
    }

    /**
     * Is channel name?
     * @param $chanName
     * @return bool
     */
    public function isChannelName($chanName)
    {
        return isset($this->_data[self::K_CHAN][$chanName]);
    }

    /**
     * Is channel alias?
     * @param string $chanName
     * @return bool
     */
    public function isChannelAlias($chanName)
    {
        return isset($this->_data[self::K_CHAN_ALIAS][$chanName]);
    }

    /**
     * Is channel uri?
     * @param $uri
     * @return bool
     */
    public function isChannelUri($uri)
    {
        $uri = $this->formatUri($uri);
        return isset($this->_data[self::K_CHAN_URI][$uri]);
    }

    /**
     * Unset channel uri record
     * @param string $uri
     * @return void
     */
    protected function unsetChannelUriRecord($uri)
    {
        $uri = $this->formatUri($uri);
        unset($this->_data[self::K_CHAN_URI][$uri]);
    }

    /**
     * Set channel uri record: uri maps to channel record
     * @param string $chanName
     * @param string $uri
     * @return void
     */
    protected function setChannelUriRecord($chanName, $uri)
    {
        $uri = $this->formatUri($uri);
        $this->_data[self::K_CHAN_URI][$uri] = $chanName;
    }

    /**
     * Get channel name by uri record
     * @param string $uri
     * @return string
     */
    protected function getChannelUriRecord($uri)
    {
        $uri = $this->formatUri($uri);
        return $this->_data[self::K_CHAN_URI][$uri];
    }


    /**
     * Unset channel record
     * @param string $chanName
     * @return void
     */
    protected function unsetChannelRecord($chanName)
    {
        unset($this->_data[self::K_CHAN][$chanName]);
    }

    /**
     * Get channel record
     * @param string $chanName
     * @return array
     */
    protected function getChannelRecord($chanName)
    {
        return $this->_data[self::K_CHAN][$chanName];
    }

    /**
     * Set channel record
     * @param string $chanName
     * @param string $uri
     * @param mixed $data
     * @param array $packages
     * @return void
     */
    protected function setChannelRecord($chanName, $uri, $data, $packages = array())
    {
        $this->_data[self::K_CHAN][$chanName] = array(
        self::K_NAME=>$chanName,
        self::K_URI=>$uri,
        self::K_CHAN_DATA=>$data,
        self::K_PACK=>$packages
        );
    }

    /**
     * Set package record
     * @param string $chanName
     * @param string $packageName
     * @param mixed $data
     * @return void
     */
    protected function setPackageRecord($chanName, $packageName, $data, $oneField = null)
    {
        if(null === $oneField) {
            $this->_data[self::K_CHAN][$chanName][self::K_PACK][$packageName] = $data;
        } else {
            $this->_data[self::K_CHAN][$chanName][self::K_PACK][$packageName][$oneField] = $data;
        }
    }



    /**
     * Unset package record
     * @param string $chanName
     * @param string $packageName
     * @return void
     */
    protected function unsetPackageRecord($chanName, $packageName)
    {
        unset($this->_data[self::K_CHAN][$chanName][self::K_PACK][$packageName]);
    }

    /**
     * Get package record
     * @param string $chanName
     * @param string $packageName
     * @return array
     */
    protected function fetchPackage($chanName, $packageName, $field = null)
    {
        if(null === $field) {
            return $this->_data[self::K_CHAN][$chanName][self::K_PACK][$packageName];
        } else {
            return $this->_data[self::K_CHAN][$chanName][self::K_PACK][$packageName][$field];
        }
    }

    /**
     * Has package record
     * @param string $chanName
     * @param string $packageName
     * @return bool
     */
    protected function hasPackageRecord($chanName, $packageName)
    {
        return isset($this->_data[self::K_CHAN][$chanName][self::K_PACK][$packageName]);
    }

    /**
     * Get channel name by alias
     * @param string $alias
     * @return array
     */
    protected function getChannelNameByAlias($alias)
    {
        return $this->_data[self::K_CHAN_ALIAS][$alias];
    }

    /**
     * Set channel alias
     * @param string $alias
     * @param string $chanName
     * @return void
     */
    protected function setChannelAlias($alias, $chanName)
    {
        $this->_data[self::K_CHAN_ALIAS][$alias] = $chanName;
    }

    /**
     * Unset channel alias
     * @param string $alias
     * @return void
     */
    protected function unsetChannelAlias($alias)
    {
        unset($this->_data[self::K_CHAN_ALIAS][$alias]);
    }

    /**
     * Clear all aliases of channel
     * @param string $chanName channel name
     * @return void
     */
    protected function clearAliases($chanName)
    {
        $keys = array_keys($this->_data[self::K_CHAN_ALIAS]);
        foreach ($keys as $key) {
            if($this->_data[self::K_CHAN_ALIAS][$key] == $chanName) {
                unset($this->_data[self::K_CHAN_ALIAS][$key]);
            }
        }
    }

    /**
     * Add channel alias
     * @param string $chanName
     * @param string $alias
     * @return void
     */
    public function addChannelAlias($chanName, $alias)
    {
        if($this->isChannelName($alias)) {
            return $this->doError("Alias '{$alias}' is existant channel name!");
        }

        if(!$this->isChannelName($chanName)) {
            return $this->doError("Channel '{$chanName}' doesn't exist");
        }
        $this->setChannelAlias($alias, $chanName);
        $this->save();
    }



    /**
     * Add channel
     * @param $chanName
     * @param $uri
     * @param $data
     * @return void
     */
    public function addChannel($chanName, $uri, $data = array())
    {
        if($this->isChannelName($chanName)) {
            return $this->doError("Channel '{$chanName}' already exist!");
        }
        if($this->isChannelUri($uri)) {
            return $this->doError("Channel with uri= '{$uri}' already exist!");
        }
        if($this->isChannelAlias($chanName)) {
            $this->unsetChannelAlias($chanName);
        }
        $uri = $this->formatUri($uri);
        $this->setChannelRecord($chanName, $uri, $data);
        $this->setChannelUriRecord($chanName, $uri);
        $this->save();
    }



    /**
     * Delete channel
     * @param $chanName
     * @return void
     */
    public function deleteChannel($chanName)
    {
        if($this->isChannelName($chanName)) {
            $record = $this->getChannelRecord($chanName);
            $this->unsetChannelUriRecord($record[self::K_URI]);
            $this->unsetChannelRecord($chanName);
            $this->clearAliases($chanName);
        } elseif($this->isChannelUri($chanName)) {
            $uri = $chanName;
            $chanName = $this->getChannelUriRecord($uri);
            $this->unsetChannelUriRecord($uri);
            $this->unsetChannelRecord($chanName);
            $this->clearAliases($chanName);
        } elseif($this->isChannelAlias($chanName)) {
            $this->unsetChannelAlias($chanName);
        } else {
            return $this->doError("'{$chanName}' was not found in aliases, channel names, channel uris");
        }
        $this->save();
    }


    /**
     * Converts channel name, url or alias to channel name
     * throws exception if not found
     * @param srting $chanName
     * @return string
     */
    public function chanName($chanName)
    {
        $channelData = $this->getChannel($chanName);
        if(!$channelData) {
            return $this->doError("Channel '{$chanName}' doesn't exist");
        }
        return $channelData[self::K_NAME];
    }

    public function chanUrl($chan)
    {
        $channelData = $this->getChannel($chan);
        if(!$channelData) {
            return $this->doError("Channel '{$chan}' doesn't exist");
        }
        return $channelData[self::K_URI];
    }


    /**
     * Add package
     * @param \Magento\Connect\Package $package
     * @return void
     */
    public function addPackage($package)
    {
        $channel = $this->chanName($package->getChannel());
        $name = $package->getName();
        $record = array (
        self::K_VER => $package->getVersion(),
        self::K_STATE => $package->getStability(),
        self::K_XML => $package->getPackageXml(),
        self::K_NAME => $name,
        self::K_DEPS => array(),
        self::K_PACK_DEPS => array(),
        );
        $this->setPackageRecord($channel, $name, $record);
        $this->setPackageDependencies($channel, $name, $package->getDependencyPackages());
        $this->save();
    }




    /**
     * Delete package
     * @param string $chanName
     * @param string $package
     * @return void
     */
    public function deletePackage($chanName, $package)
    {
        $chanName = $this->chanName($chanName);
        $this->unsetPackageRecord($chanName, $package);
        $this->save();
    }

    /**
     * Get package
     * @param sting $chanName
     * @param string $package
     * @return void
     */
    public function getPackage($chanName, $package)
    {
        $chanName = $this->chanName($chanName);
        if($this->hasPackageRecord($chanName, $package)) {
            return $this->fetchPackage($chanName, $package);
        }
        return null;
    }

    public function getPackageObject($chanName, $package)
    {
        $chanName = $this->chanName($chanName);
        if($this->hasPackageRecord($chanName, $package)) {
            $data = $this->fetchPackage($chanName, $package);
            return new \Magento\Connect\Package($data[self::K_XML]);
        }
        throw new \Exception("Cannot get package: '{$package}'");
    }


    public function hasPackage($chanName, $package, $versionMin = false, $versionMax = false)
    {
        $chanName = $this->chanName($chanName);
        $data = $this->getPackage($chanName, $package);
        if(null === $data) {
            return false;
        }
        $installedVersion = $data[self::K_VER];
        return $this->versionInRange($installedVersion, $versionMin, $versionMax);
    }

    public function versionInRange($version, $versionMin = false, $versionMax = false)
    {
        if(false === $versionMin) {
            $minOk = true;
        } else {
            $minOk = version_compare($version, $versionMin, ">=");
        }
        if(false === $versionMax) {
            $maxOk = true;
        } else {
            $maxOk = version_compare($version, $versionMax, "<=");
        }
        return $minOk && $maxOk;
    }

    public function hasVersionRangeIntersect($min1, $max1, $min2, $max2)
    {
        if(version_compare($min1, $min2, ">") && version_compare($max1, $max2, ">")) {
            return false;
        } elseif(version_compare($min1, $min2, "<") && version_compare($max1, $max2, "<")) {
            return false;
        } elseif(version_compare($min1, $min2, ">=") && version_compare($max1, $max2, "<=")) {
            return true;
        } elseif(version_compare($min1, $min2, "<=") && version_compare($max1, $max2, ">=")) {
           return true;            
        }
        return false;        
    }

    /**
     * Clear contents to defaults and save
     * @return void
     */
    public function clear()
    {
        $this->setEmptyConfig();
        $this->save();
    }

    /**
     * Output error - throw exception
     * @param $message
     * @throws \Exception
     * @return void
     */
    protected function doError($message)
    {
        throw new \Exception($message);
    }





    public function compareStabilities($s1, $s2)
    {
        if(!$this->_validator) {
            $this->_validator = new \Magento\Connect\Validator();
        }
        return $this->_validator->compareStabilities($s1, $s2);
    }



    public function detectVersionFromRestArray($restData, $argVersionMin = false, $argVersionMax = false, $preferredStability = 'devel')
    {

        if(!is_array($restData)) {
            return false;
        }

        foreach($restData as $vData) {
            $stability = trim($vData['s']);
            $version = trim($vData['v']);
            $goodStability = $this->compareStabilities($stability, $preferredStability) >= 0;
            if($goodStability && $this->versionInRange($version, $argVersionMin, $argVersionMax)) {
                return $version;
            }
        }
        return false;
    }


    public function setPackageDependencies($chanName, $package, $data)
    {
        $chanName = $this->chanName($chanName);
        if($this->hasPackageRecord($chanName, $package)) {
            $this->setPackageRecord($chanName, $package, $data, self::K_PACK_DEPS);
            $this->save();
            return true;
        }
        return false;
    }

    public function getPackageDependencies($chanName, $package)
    {
        $chanName = $this->chanName($chanName);
        if($this->hasPackageRecord($chanName, $package)) {
            return $this->fetchPackage($chanName, $package, self::K_PACK_DEPS);
        }
        return false;
    }



    public function setDependencyInfo($chanName, $package, $data)
    {
        $chanName = $this->chanName($chanName);
        if($this->hasPackageRecord($chanName, $package)) {
            $this->setPackageRecord($chanName, $package, $data, self::K_DEPS);
            $this->save();
            return true;
        }
        return false;
    }

    public function getDependencyInfo($chanName, $package)
    {
        $chanName = $this->chanName($chanName);
        if($this->hasPackageRecord($chanName, $package)) {
            return $this->fetchPackage($chanName, $package, self::K_DEPS);
        }
        return false;
    }



    public function getChannelNames()
    {
        return array_keys($this->_data[self::K_CHAN]);
    }

    public function getPackagesData($channel = false)
    {
        if(false == $channel) {
            return $this->_data[self::K_CHAN];
        }

        if(!$this->isChannel($channel)) {
            return array();
        }
        return $this->getChannel($channel);
    }

    public function specifiedInDependencyList($deps, $chanName, $packageName)
    {
        foreach($deps as $dep) {
            if($chanName == $dep['channel'] && $packageName == $dep['name']) {
                return true;
            }
        }
        return false;
    }

    public function requiredByOtherPackages($chanName, $packageName, $excludeList = array())
    {
        $out = array();
        foreach($this->_data[self::K_CHAN] as $channel=>$data) {
            foreach($data[self::K_PACK] as $package) {
                if($this->specifiedInDependencyList($excludeList, $channel, $package['name'])) {
                    continue;
                }
                $deps = $package[self::K_PACK_DEPS];
                if($this->specifiedInDependencyList($deps, $chanName, $packageName)) {
                    $out[] = array('channel'=>$channel, 'name' =>$package['name'], 'version'=>$package['version']); 
                }               
            }
        }
        return $out;
    }




    public function getInstalledPackages($chanName = false)
    {
        if(false == $chanName) {
            $data = $this->getChannelNames();
        } elseif($this->isChannel($chanName)) {
            $tmp = $this->getChannel($chanName);
            $data = array($tmp[self::K_NAME]);
        }
        $out = array();
        foreach( $data as $chanName) {
            $channel = $this->getChannel($chanName);
            $out[$chanName] = array();
            foreach($channel[self::K_PACK] as $package=>$data) {
                $out[$chanName][$package] = array();
                foreach(array(self::K_VER, self::K_STATE) as $k) {
                    $out[$chanName][$package][$k] = $data[$k];
                }
            }
        }
        return $out;
    }


    
    /**
     * Check if package conflicts with installed packages
     * Returns:
     *    array with conflicts
     *    false if no conflicts
     *
     * @param string $chanName
     * @param string $packageName
     * @param string $version
     * @return array|false
     */
    public function hasConflicts($chanName, $packageName, $version)
    {
        $conflicts = array();
        foreach($this->_data[self::K_CHAN] as $channel=>$data) {
            foreach($data[self::K_PACK] as $package) {
                if($channel != $chanName) {
                    continue;
                }
                $deps = $package[self::K_PACK_DEPS];
                foreach($deps as $dep) {
                    if($dep['name'] != $packageName) {
                        continue;
                    }

                    if(!$this->versionInRange($version, $dep['min'], $dep['max'])) {
                        //var_dump($version, $dep['min'], $dep['max']);
                        $conflicts[] = $channel . "/". $package['name'] ." ". $package['version'];
                    }
                }
            }
        }
        return count($conflicts) ? $conflicts : false;
    }




}
