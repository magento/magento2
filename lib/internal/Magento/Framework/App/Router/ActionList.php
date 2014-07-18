<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App\Router;

class ActionList
{
    /**
     * List of application actions
     *
     * @var array
     */
    protected $actions;

    /**
     * @var array
     */
    protected $reservedWords;

    /**
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param ActionList\Reader $actionReader
     * @param string $actionInterface
     * @param string $cacheKey
     * @param array $reservedWords
     */
    public function __construct(
        \Magento\Framework\Config\CacheInterface $cache,
        ActionList\Reader $actionReader,
        $actionInterface = '\Magento\Framework\App\ActionInterface',
        $cacheKey = 'app_action_list',
        $reservedWords = array('new', 'switch', 'return', 'print', 'list')
    ) {
        $this->reservedWords = $reservedWords;
        $this->actionInterface = $actionInterface;
        $data = $cache->load($cacheKey);
        if (!$data) {
            $this->actions = $actionReader->read();
            $cache->save(serialize($this->actions), $cacheKey);
        } else {
            $this->actions = unserialize($data);
        }
    }

    /**
     * Retrieve action class
     *
     * @param string $module
     * @param string $area
     * @param string $namespace
     * @param string $action
     * @return null|string
     */
    public function get($module, $area, $namespace, $action)
    {
        if ($area) {
            $area = '\\' . $area;
        }
        if (in_array(strtolower($action), $this->reservedWords)) {
            $action .= 'action';
        }
        $fullPath = str_replace('_', '\\', strtolower(
            $module . '\\controller' . $area . '\\' . $namespace . '\\' . $action
        ));
        if (isset($this->actions[$fullPath])) {
            return is_subclass_of($this->actions[$fullPath], $this->actionInterface) ? $this->actions[$fullPath] : null;
        }
        return null;
    }
}
