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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Plugin;

class IndexerConfigData
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State
     */
    protected $_state;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $state
     */
    public function __construct(\Magento\Catalog\Model\Indexer\Product\Flat\State $state)
    {
        $this->_state = $state;
    }

    /**
     * Around get handler
     *
     * @param \Magento\Indexer\Model\Config\Data $subject
     * @param callable $proceed
     * @param string $path
     * @param string $default
     *
     * @return mixed|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     */
    public function aroundGet(
        \Magento\Indexer\Model\Config\Data $subject,
        \Closure $proceed,
        $path = null,
        $default = null
    ) {
        $data = $proceed($path, $default);

        if (!$this->_state->isFlatEnabled()) {
            $indexerId = \Magento\Catalog\Model\Indexer\Product\Flat\Processor::INDEXER_ID;
            if (!$path && isset($data[$indexerId])) {
                unset($data[$indexerId]);
            } elseif ($path) {
                list($firstKey, ) = explode('/', $path);
                if ($firstKey == $indexerId) {
                    $data = $default;
                }
            }
        }

        return $data;
    }
}
