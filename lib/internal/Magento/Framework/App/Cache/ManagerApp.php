<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Cache;

use Magento\Framework\App;
use Magento\Framework\App\Console\Response;
use Magento\Framework\AppInterface;

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
    const KEY_STATUS = 'status';
    /**#@- */

    /**
     * Console response
     *
     * @var Response
     */
    private $response;

    /**
     * Requested changes
     *
     * @var array
     */
    private $requestArgs;

    /**
     * Cache manager
     *
     * @var Manager
     */
    private $cacheManager;

    /**
     * Constructor
     *
     * @param Manager $cacheManager
     * @param Response $response
     * @param array $requestArgs
     */
    public function __construct(
        Manager $cacheManager,
        Response $response,
        array $requestArgs
    ) {
        $this->cacheManager = $cacheManager;
        $this->response = $response;
        $this->requestArgs = $requestArgs;
    }

    /**
     * {@inheritdoc}
     * @return Response
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function launch()
    {
        $output = [];
        $types = $this->getRequestedTypes();

        $enabledTypes = [];
        if (isset($this->requestArgs[self::KEY_SET])) {
            $isEnabled = (bool)(int)$this->requestArgs[self::KEY_SET];
            $changedTypes = $this->cacheManager->setEnabled($types, $isEnabled);
            if ($isEnabled) {
                $enabledTypes = $changedTypes;
            }
            if ($changedTypes) {
                $output[] = 'Changed cache status:';
                foreach ($changedTypes as $type) {
                    $output[] = sprintf('%30s: %d -> %d', $type, !$isEnabled, $isEnabled);
                }
            } else {
                $output[] = 'There is nothing to change in cache status';
            }
        }
        if (isset($this->requestArgs[self::KEY_FLUSH])) {
            $this->cacheManager->flush($types);
            $output[] = 'Flushed cache types:';
            $output[] = join("\n", $types);
        } elseif (isset($this->requestArgs[self::KEY_CLEAN])) {
            $this->cacheManager->clean($types);
            $output[] = 'Cleaned cache types:';
            $output[] = join("\n", $types);
        } elseif (isset($this->requestArgs[self::KEY_STATUS])) {
            $output[] = 'Current status:';
            foreach ($this->cacheManager->getStatus() as $cache => $status) {
                $output[] = sprintf('%30s: %d', $cache, $status);
            }
        } elseif (!empty($enabledTypes)) {
            $this->cacheManager->clean($enabledTypes);
            $output[] = 'Cleaned cache types:';
            $output[] = join("\n", $enabledTypes);
        }

        $output[] = '';
        $this->response->setBody(join("\n", $output));
        return $this->response;
    }

    /**
     * Maps requested type from request into the current registry of types
     *
     * @return string[]
     * @throws \InvalidArgumentException
     */
    private function getRequestedTypes()
    {
        $requestedTypes = [];
        if (isset($this->requestArgs[self::KEY_TYPES])) {
            $requestedTypes = explode(',', $this->requestArgs[self::KEY_TYPES]);
            $requestedTypes = array_filter(array_map('trim', $requestedTypes), 'strlen');
        }
        $availableTypes = $this->cacheManager->getAvailableTypes();
        if (empty($requestedTypes)) {
            return $availableTypes;
        } else {
            $unsupportedTypes = array_diff($requestedTypes, $availableTypes);
            if ($unsupportedTypes) {
                throw new \InvalidArgumentException(
                    "The following requested cache types are not supported: '" . join("', '", $unsupportedTypes)
                    . "'.\nSupported types: " . join(", ", $availableTypes) . ""
                );
            }
            return array_values(array_intersect($availableTypes, $requestedTypes));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function catchException(App\Bootstrap $bootstrap, \Exception $exception)
    {
        $this->response->setBody($exception->getMessage());
        $this->response->terminateOnSend(false);
        $this->response->sendResponse();
        return false;
    }
}
