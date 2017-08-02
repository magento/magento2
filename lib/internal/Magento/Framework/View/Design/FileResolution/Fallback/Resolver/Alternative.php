<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback\Resolver;

use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\View\Design\Fallback\Rule\RuleInterface;
use Magento\Framework\View\Design\FileResolution\Fallback;

/**
 * Resolver for view files with support of alternative extensions map
 * @since 2.0.0
 */
class Alternative extends Simple
{
    /**
     * @var array
     * @since 2.0.0
     */
    private $alternativeExtensions;

    /**
     * Constructor
     *
     * @param ReadFactory $readFactory
     * @param \Magento\Framework\View\Design\Fallback\RulePool $rulePool
     * @param array $alternativeExtensions
     * @since 2.0.0
     */
    public function __construct(
        ReadFactory $readFactory,
        \Magento\Framework\View\Design\Fallback\RulePool $rulePool,
        array $alternativeExtensions = []
    ) {
        foreach ($alternativeExtensions as $extension => $newExtensions) {
            if (!is_string($extension) || !is_array($newExtensions)) {
                throw new \InvalidArgumentException(
                    "\$alternativeExtensions must be an array with format: "
                    . "array('ext1' => array('ext1', 'ext2'), 'ext3' => array(...)]"
                );
            }
        }
        $this->alternativeExtensions = $alternativeExtensions;
        parent::__construct($readFactory, $rulePool);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function resolveFile(RuleInterface $fallbackRule, $file, array $params = [])
    {
        $path = parent::resolveFile($fallbackRule, $file, $params);
        if (!$path) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if (isset($this->alternativeExtensions[$extension])) {
                foreach ($this->alternativeExtensions[$extension] as $newExtension) {
                    $newFile = substr($file, 0, strlen($file) - strlen($extension)) . $newExtension;
                    $result = parent::resolveFile($fallbackRule, $newFile, $params);
                    if ($result) {
                        $path = $result;
                        break;
                    }
                }
            }
        }
        return $path;
    }
}
