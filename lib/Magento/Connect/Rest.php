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
use Magento\Connect\Channel\VO;

/**
 * Class to work with remote REST interface
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Rest
{

    const CHANNELS_XML = "channels.xml";
    const CHANNEL_XML = "channel.xml";
    const PACKAGES_XML = "packages.xml";
    const RELEASES_XML = "releases.xml";
    const PACKAGE_XML = "package.xml";
    const EXT = "tgz";

    /**
     * HTTP Loader
     * @var \Magento\HTTP\IClient
     */
    protected $_loader = null;

    /**
     * XML parser
     * @var \Magento\Xml\Parser
     */
    protected $_parser = null;

    /**
     * Channel URI
     * @var string
     */
    protected $_chanUri = '';

    /**
     * Protocol HTTP or FTP
     *
     * @var string http or ftp
     */
    protected $_protocol = '';

    /**
     * Constructor
     *
     * @param string $protocol
     */
    public function __construct($protocol="http")
    {
        switch ($protocol) {
            case 'http':default:
                $this->_protocol = 'http';
                break;
            case 'ftp':
                $this->_protocol = 'ftp';
                break;
        }
    }

    /**
     * Set channel info
     *
     * @param string $uri
     * @return void
     */
    public function setChannel($uri)
    {
        $this->_chanUri = $uri;
    }

    /**
     * Get HTTP loader
     * @return \Magento\Connect\Loader
     */
    protected function getLoader()
    {
        if(is_null($this->_loader)) {
            $this->_loader = \Magento\Connect\Loader::getInstance($this->_protocol);
        }
        return $this->_loader;
    }


    /**
     * Get parser
     *
     * @return \Magento\Xml\Parser
     */
    protected function getParser()
    {
        if(is_null($this->_parser)) {
            $this->_parser = new \Magento\Xml\Parser();
        }
        return $this->_parser;
    }

    /**
     * Load URI response
     * @param string $uriSuffix
     * @return false|string
     */
    protected function loadChannelUri($uriSuffix)
    {
        $url = $this->_chanUri."/".$uriSuffix;
        //print $url."\n";
        $this->getLoader()->get($url);
        $statusCode = $this->getLoader()->getStatus();
        if($statusCode != 200) {
            return false;
        }
        return $this->getLoader()->getBody();
    }

    /**
     * Get channels list of URI
     *
     * @return VO
     * @throws \Exception
     */
    public function getChannelInfo()
    {
        $out = $this->loadChannelUri(self::CHANNEL_XML);
        $statusCode = $this->getLoader()->getStatus();
        if($statusCode != 200) {
            throw new \Exception("Invalid server response for {$this->_chanUri}");
        }
        $parser = $this->getParser();
        $out = $parser->loadXML($out)->xmlToArray();

        // TODO: add channel validator
        $vo = new VO();
        $vo->fromArray($out['channel']);
        if(!$vo->validate()) {
            throw new \Exception("Invalid channel.xml file");
        }
        return $vo;
    }


    /**
     * Get packages list of channel
     *
     * @return array|false
     */
    public function getPackages()
    {
        $out = $this->loadChannelUri(self::PACKAGES_XML);
        $statusCode = $this->getLoader()->getStatus();
        if($statusCode != 200) {
            return false;
        }
        $parser = $this->getParser();
        $out = $parser->loadXML($out)->xmlToArray();


        if(!isset($out['data']['p'])) {
            return array();
        }
        if(isset($out['data']['p'][0])) {
            return $out['data']['p'];
        }
        if(is_array($out['data']['p'])) {
            return array($out['data']['p']);
        }
        return array();
    }

    /**
     * @return array|false
     */
    public function getPackagesHashed()
    {
        $out = $this->loadChannelUri(self::PACKAGES_XML);
        $statusCode = $this->getLoader()->getStatus();
        if($statusCode != 200) {
            return false;
        }
        $parser = $this->getParser();
        $out = $parser->loadXML($out)->xmlToArray();

        $return = array();
        if(!isset($out['data']['p'])) {
            return $return;
        }
        if(isset($out['data']['p'][0])) {
            $return = $out['data']['p'];
        }
        if(is_array($out['data']['p'])) {
            $return = array($out['data']['p']);
        }
        $c  = count($return);
        if($c) {
            $output = array();
            for($i=0,$c=count($return[0]); $i<$c; $i++) {
                $element = $return[0][$i];
                $output[$element['n']] = $element['r'];
            }
            $return = $output;
        }

        $out = array();
        foreach($return as $name=>$package) {
            $stabilities = array_map(array($this, 'shortStateToLong'), array_keys($package));
            $versions = array_map('trim', array_values($package));
            $package = array_combine($versions, $stabilities);
            ksort($package);
            $out[$name] = $package;
        }
        return $out;
    }

    /**
     * Stub
     * @param string $n
     * @return string
     */
    public function escapePackageName($n)
    {
        return $n;
    }

    /**
     * Get releases list of package on current channel
     * @param string $package package name
     * @return false|array
     */
    public function getReleases($package)
    {
        $out = $this->loadChannelUri($this->escapePackageName($package)."/".self::RELEASES_XML);
        $statusCode = $this->getLoader()->getStatus();
        if($statusCode != 200) {
            return false;
        }
        $parser = $this->getParser();
        $out = $parser->loadXML($out)->xmlToArray();
        if(!isset($out['releases']['r'])) {
            return array();
        }
        $src = $out['releases']['r'];
        if(!array_key_exists(0, $src)) {
            return array($src);
        }
        $this->sortReleases($src);
        return $src;
    }

    /**
     * Sort releases
     * @param array &$releases
     * @return void
     */
    public function sortReleases(array &$releases)
    {
        usort($releases, array($this, 'sortReleasesCallback'));
        $releases = array_reverse($releases);
    }

    /**
     * Sort releases callback
     * @param string $a
     * @param string $b
     * @return int
     */
    protected function sortReleasesCallback($a, $b)
    {
        return version_compare($a['v'], $b['v']);
    }

    /**
     * Get package info (package.xml)
     *
     * @param string $package
     * @return \Magento\Connect\Package
     */
    public function getPackageInfo($package)
    {
        $out = $this->loadChannelUri($this->escapePackageName($package)."/".self::PACKAGE_XML);
        if(false === $out) {
            return false;
        }
        return new \Magento\Connect\Package($out);
    }

    /**
     *
     * @param string $package
     * @param string $version
     * @return \Magento\Connect\Package
     */
    public function getPackageReleaseInfo($package, $version)
    {
        $out = $this->loadChannelUri($this->escapePackageName($package)."/".$version."/".self::PACKAGE_XML);
        if(false === $out) {
            return false;
        }
        return new \Magento\Connect\Package($out);
    }

    /**
     * Get package archive file of release
     *
     * @param string $package package name
     * @param string $version version
     * @param string $targetFile
     * @return true|void
     * @throws \Exception
     */
    public function downloadPackageFileOfRelease($package, $version, $targetFile)
    {
        $package = $this->escapePackageName($package);
        $version = $this->escapePackageName($version);


        if(file_exists($targetFile)) {
            $chksum = $this->loadChannelUri($package."/".$version."/checksum");
            $statusCode = $this->getLoader()->getStatus();
            if($statusCode == 200) {
                if(md5_file($targetFile) == $chksum) {
                    return true;
                }
            }
        }


        $out = $this->loadChannelUri($package."/".$version."/".$package."-".$version.".".self::EXT);

        $statusCode = $this->getLoader()->getStatus();
        if($statusCode != 200)	{
            throw new \Exception("Package not found: {$package} {$version}");
        }
        $dir = dirname($targetFile);
        @mkdir($dir, 0777, true);
        $result = @file_put_contents($targetFile, $out);
        if(false === $result) {
            throw new \Exception("Cannot write to file {$targetFile}");
        }
        return true;
    }

    /**
     * @var array
     */
    protected $states = array('b'=>'beta', 'd'=>'dev', 's'=>'stable', 'a'=>'alpha');

    /**
     * @param string $s
     * @return string
     */
    public function shortStateToLong($s)
    {
        return isset($this->states[$s]) ? $this->states[$s] : 'dev';
    }


}

