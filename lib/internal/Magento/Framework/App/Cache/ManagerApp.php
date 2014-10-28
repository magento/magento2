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

namespace Magento\Framework\App\Cache;

use Magento\Framework\App;
use Magento\Framework\AppInterface;
use Magento\Framework\App\Console\Response;

/**
 * An application for managing cache status
 */
class ManagerApp implements AppInterface
{
    /**#@+
     * Request keys for managing caches
     */
    const KEY_TYPES = 'types';
    const KEY_SET = 'set';
    const KEY_CLEAN = 'clean';
    const KEY_FLUSH = 'flush';
    /**#@- */

    /**
     * Cache types list
     *
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * Cache state service
     *
     * @var StateInterface
     */
    private $cacheState;

    /**
     * Console response
     *
     * @var Response
     */
    private $response;

    /**
     * Cache types pool
     *
     * @var Type\FrontendPool
     */
    private $pool;

    /**
     * Requested changes
     *
     * @var array
     */
    private $request;

    /**
     * Constructor
     *
     * @param TypeListInterface $cacheTypeList
     * @param StateInterface $cacheState
     * @param Response $response
     * @param Type\FrontendPool $pool
     * @param array $request
     */
    public function __construct(
        TypeListInterface $cacheTypeList,
        StateInterface $cacheState,
        Response $response,
        Type\FrontendPool $pool,
        array $request
    ) {
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheState = $cacheState;
        $this->response = $response;
        $this->pool = $pool;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     * @return Response
     */
    public function launch()
    {
        $this->response->terminateOnSend(false);
        $types = $this->getRequestedTypes();
        $queue = $this->updateStatus($types);
        $this->clean($queue, $types);
        return $this->response;
    }

    /**
     * Maps requested type from request into the current registry of types
     *
     * @return string[]
     */
    private function getRequestedTypes()
    {
        $requested = isset($this->request[self::KEY_TYPES]) ? explode(',', $this->request[self::KEY_TYPES]) : [];
        $result = [];
        foreach (array_keys($this->cacheTypeList->getTypes()) as $type) {
            if (empty($requested) || in_array($type, $requested)) {
                $result[] = $type;
            }
        }
        return $result;
    }

    /**
     * Updates cache status for the requested types
     *
     * @param string[] $types
     * @return string[] Queue of affected cache types that need cleanup
     */
    private function updateStatus($types)
    {
        if (!isset($this->request[self::KEY_SET])) {
            return [];
        }
        $isEnabled = (bool)(int)$this->request[self::KEY_SET];
        $isUpdated = false;
        $cleanQueue = [];
        foreach ($types as $type) {
            if ($this->cacheState->isEnabled($type) === $isEnabled) { // no need to poke it, if is not going to change
                continue;
            }
            $this->cacheState->setEnabled($type, $isEnabled);
            $isUpdated = true;
            if ($isEnabled) {
                $cleanQueue[] = $type;
            }
        }
        if ($isUpdated) {
            $this->cacheState->persist();
        }
        return $cleanQueue;
    }

    /**
     * Cleans up or flushes caches (depending on what was requested)
     *
     * Types listed at the "required" argument are mandatory to clean.
     * But try flush first, as it is more efficient. So if something was requested or required and flushed, then there
     * is no need to clean it anymore.
     *
     * @param string[] $required
     * @param string[] $requested
     * @return void
     */
    private function clean($required, $requested)
    {
        $flushed = $this->flush($requested);
        if (isset($this->request[self::KEY_CLEAN])) {
            $types = array_merge($requested, $required);
        } else {
            $types = $required;
        }
        foreach ($types as $type) {
            $frontend = $this->pool->get($type);
            if (in_array($type, $flushed)) { // it was already flushed
                continue;
            }
            $frontend->clean();
        }
    }

    /**
     * Flushes specified cache storages
     *
     * Returns array of types which were flushed
     *
     * @param string[] $types
     * @return string[]
     */
    private function flush($types)
    {
        $result = [];
        if (isset($this->request[self::KEY_FLUSH])) {
            foreach ($types as $type) {
                $frontend = $this->pool->get($type);
                $backend = $frontend->getBackend();
                if (in_array($backend, $result, true)) { // it was already flushed from another frontend
                    continue;
                }
                $backend->clean();
                $result[$type] = $backend;
            }
        }
        return array_keys($result);
    }

    /**
     * Presents summary about cache status
     *
     * @return array
     */
    public function getStatusSummary()
    {
        $result = [];
        foreach ($this->cacheTypeList->getTypes() as $type) {
            $result[$type['id']] = $type['status'];
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function catchException(App\Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }
}
