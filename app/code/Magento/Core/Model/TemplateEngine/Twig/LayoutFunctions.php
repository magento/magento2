<?php
/**
 * Layout functions needed for twig extension
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\TemplateEngine\Twig;

class LayoutFunctions
{

    /**
     * @var \Magento\Core\Model\Layout
     */
    private $_layout;

    /**
     * @var \Magento\Core\Model\TemplateEngine\BlockTrackerInterface
     */
    private $_blockTracker;

    public function __construct(
        \Magento\Core\Model\Layout $layout
    ) {
        $this->_layout = $layout;
    }

    /**
     * Sets the block tracker that is needed for dynamically determining the child html at runtime
     *
     * @param \Magento\Core\Model\TemplateEngine\BlockTrackerInterface $blockTracker
     */
    public function setBlockTracker(\Magento\Core\Model\TemplateEngine\BlockTrackerInterface $blockTracker)
    {
        $this->_blockTracker = $blockTracker;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        $options = array('is_safe' => array('html'));
        return array(
            new \Twig_SimpleFunction('getBlockData', array($this, 'getBlockData'), $options),
            new \Twig_SimpleFunction('getMessagesHtml',
                array($this->_layout->getMessagesBlock(), 'getGroupedHtml'), $options),
            new \Twig_SimpleFunction('executeRenderer', array($this->_layout, 'executeRenderer'), $options),
            new \Twig_SimpleFunction('getChildHtml', array($this, 'getChildHtml'), $options),
            new \Twig_SimpleFunction('getGroupChildNames', array($this, 'getGroupChildNames'), $options),
            new \Twig_SimpleFunction('getBlockNameByAlias', array($this, 'getBlockNameByAlias'), $options),
            new \Twig_SimpleFunction('createBlock', array($this->_layout, 'createBlock')),
            new \Twig_SimpleFunction('getElementAlias', array($this->_layout, 'getElementAlias'), $options),
            new \Twig_SimpleFunction('renderElement', array($this->_layout, 'renderElement'), $options),
        );
    }

    /**
     * Returns data assigned to the block instance
     *
     * @param $name
     * @param string $key
     * @return mixed|null
     */
    public function getBlockData($name, $key = '')
    {
        $block = $this->_layout->getBlock($name);
        if ($block) {
            return $block->getData($key);
        }
        return null;
    }

    /**
     * Render Block defined by the alias from the parent block defined by the name
     *
     * @param $name
     * @param string $alias
     * @param bool $useCache
     * @return string
     */
    public function renderBlock($name, $alias = '', $useCache = true)
    {
        $out = '';
        if ($alias) {
            $childName = $this->_layout->getChildName($name, $alias);
            if ($childName) {
                $out = $this->_layout->renderElement($childName, $useCache);
            }
        } else {
            foreach ($this->_layout->getChildNames($name) as $child) {
                $out .= $this->_layout->renderElement($child, $useCache);
            }
        }

        return $out;
    }

    /**
     * Render children of the current block defined by alias
     *
     * @param string $alias
     * @param bool $useCache
     * @return string
     */
    public function getChildHtml($alias = '', $useCache = true)
    {
        $name = $this->_blockTracker->getCurrentBlock()->getNameInLayout();
        return $this->renderBlock($name, $alias, $useCache);
    }

    /**
     * Get a group of child blocks
     *
     * Returns an array of <alias> => <block>
     * or an array of <alias> => <callback_result>
     * The callback currently supports only $this methods and passes the alias as parameter
     *
     * @param string $parentName
     * @param string $groupName
     * @return array
     */
    public function getGroupChildNames($parentName, $groupName)
    {
        return $this->_layout->getGroupChildNames($parentName, $groupName);
    }

    /**
     * Get name of the block defined by alias in context of parent block defined by name
     *
     * @param $parentName
     * @param $alias
     * @return bool|string
     */
    public function getBlockNameByAlias($parentName, $alias)
    {
        $name = $this->_layout->getChildName($parentName, $alias);
        if (!$name) {
            return '';
        }
        return $name;
    }
}
