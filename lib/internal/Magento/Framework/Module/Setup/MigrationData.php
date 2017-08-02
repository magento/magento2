<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Setup;

/**
 * Replace patterns needed for migration process between Magento versions
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class MigrationData
{
    /**
     * List of required params
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_requiredParams = ['plain', 'wiki', 'xml', 'serialized'];

    /**
     * List of replace patterns
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_patterns = [];

    /**
     * @param array $data
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function __construct(
        array $data
    ) {
        foreach ($this->_requiredParams as $param) {
            if (!isset($data[$param])) {
                throw new \InvalidArgumentException("Missing required param " . $param);
            }
            $this->_patterns[$param] = $data[$param];
        }
    }

    /**
     * Get replace pattern
     *
     * @return string
     * @since 2.0.0
     */
    public function getPlainFindPattern()
    {
        return $this->_patterns['plain'];
    }

    /**
     * Get replace pattern
     *
     * @return string
     * @since 2.0.0
     */
    public function getWikiFindPattern()
    {
        return $this->_patterns['wiki'];
    }

    /**
     * Get replace pattern
     *
     * @return string
     * @since 2.0.0
     */
    public function getXmlFindPattern()
    {
        return $this->_patterns['xml'];
    }

    /**
     * Get replace pattern
     *
     * @return string
     * @since 2.0.0
     */
    public function getSerializedFindPattern()
    {
        return $this->_patterns['serialized'];
    }
}
