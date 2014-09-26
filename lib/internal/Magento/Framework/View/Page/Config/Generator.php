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

namespace Magento\Framework\View\Page\Config;

use Magento\Framework\View\Page\Config;

/**
 * Page config generator model
 */
class Generator
{
    /**#@+
     * Available src_type in assets
     */
    const SRC_TYPE_RESOURCE = 'resource';

    const SRC_TYPE_CONTROLLER = 'controller';

    const SRC_TYPE_URL = 'url';
    /**#@-*/

    /**
     * Virtual content type
     */
    const VIRTUAL_CONTENT_TYPE_LINK = 'link';

    /**
     * @var \Magento\Framework\View\Page\Config\Structure
     */
    protected $structure;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var array
     */
    protected $remoteAssetTypes = [
        self::SRC_TYPE_CONTROLLER => self::SRC_TYPE_CONTROLLER,
        self::SRC_TYPE_URL => self::SRC_TYPE_URL
    ];

    /**
     * @var array
     */
    protected $assetProperties = [
        'ie_condition',
    ];

    /**
     * @var array
     */
    protected $serviceAssetProperties = [
        'src',
        'src_type'
    ];

    /**
     * @param \Magento\Framework\View\Page\Config\Structure $structure
     * @param \Magento\Framework\View\Page\Config $pageConfig
     */
    public function __construct(Structure $structure, Config $pageConfig)
    {
        $this->structure = $structure;
        $this->pageConfig = $pageConfig;
    }

    /**
     * @return $this
     */
    public function process()
    {
        $this->structure->processRemoveAssets();
        $this->processAssets();
        $this->processTitle();
        $this->processMetadata();
        $this->structure->processRemoveElementAttributes();
        $this->processElementAttributes();
        $this->processBodyClasses();
        return $this;
    }

    /**
     * Add assets to page config
     *
     * @return $this
     */
    protected function processAssets()
    {
        foreach ($this->structure->getAssets() as $name => $data) {
            if (isset($data['src_type']) && in_array($data['src_type'], $this->remoteAssetTypes)) {
                $this->pageConfig->addRemotePageAsset(
                    $name,
                    self::VIRTUAL_CONTENT_TYPE_LINK,
                    $this->getAssetProperties($data)
                );
            } else {
                $this->pageConfig->addPageAsset($name, $this->getAssetProperties($data));
            }
        }
        return $this;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getAssetProperties(array $data = [])
    {
        $properties = [];
        $attributes = [];
        foreach ($data as $name => $value) {
            if (in_array($name, $this->assetProperties)) {
                $properties[$name] = $value;
            } elseif (!in_array($name, $this->serviceAssetProperties)) {
                $attributes[$name] = $value;
            }
        }
        $properties['attributes'] = $attributes;
        return $properties;
    }

    /**
     * @return $this
     */
    protected function processTitle()
    {
        $this->pageConfig->setTitle($this->structure->getTitle());
        return $this;
    }

    /**
     * @return $this
     */
    protected function processMetadata()
    {
        foreach ($this->structure->getMetadata() as $name => $content) {
            $this->pageConfig->setMetadata($name, $content);
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function processElementAttributes()
    {
        foreach ($this->structure->getElementAttributes() as $element => $attributes) {
            foreach ($attributes as $name => $value) {
                $this->pageConfig->setElementAttribute($element, $name, $value);
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function processBodyClasses()
    {
        foreach ($this->structure->getBodyClasses() as $class) {
            $this->pageConfig->addBodyClass($class);
        }
        return $this;
    }
}
