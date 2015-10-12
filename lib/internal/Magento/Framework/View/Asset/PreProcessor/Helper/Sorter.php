<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor\Helper;

use Magento\Framework\Phrase;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Sorter
 */
class Sorter implements SorterInterface
{
    /**
     * Name of directive
     */
    const DIRECTIVE = 'after';

    /**
     * @var array
     */
    private $preprocessors;

    /**
     * @var array
     */
    private $result;

    /**
     * @var string
     */
    private $lastInsert;

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function sorting(array $preprocessors)
    {
        $this->result = [];
        $this->lastInsert = null;
        $this->preprocessors = $preprocessors;

        $this->assertLooping();

        foreach ($this->preprocessors as $preprocessorKey => $preprocessor) {
            if (isset($this->result[$preprocessorKey])) {
                continue;
            }
            $this->assertDirective($preprocessor, self::DIRECTIVE);
            $this->addBeforeItem($preprocessor, $preprocessorKey);
        }

        return $this->result;
    }

    /**
     * @param array $preprocessor
     * @param string $key
     * @return void
     */
    private function addBeforeItem(array $preprocessor, $key)
    {
        if (isset($preprocessor[self::DIRECTIVE])) {
            $after = $preprocessor[self::DIRECTIVE];
            if ($this->lastInsert === $after) {
                $this->result[$key] = $preprocessor;
                $this->lastInsert = $key;
                return;
            }
            $this->addBeforeItem($this->preprocessors[$after], $after);
        }

        $this->lastInsert = $key;
        $this->result[$key] = $preprocessor;
    }

    /**
     * @param array $preprocessor
     * @param string $directive
     * @throws LocalizedException
     */
    private function assertDirective(array &$preprocessor, $directive)
    {
        if (isset($preprocessor[$directive]) && !isset($this->preprocessors[$preprocessor[$directive]])) {
            throw new LocalizedException(
                new Phrase('Specified does not exist preprocessor in the directive "after".')
            );
        }
    }

    /**
     * @throws LocalizedException
     */
    private function assertLooping()
    {
        foreach ($this->preprocessors as $preprocessor) {
            if (!isset($preprocessor[self::DIRECTIVE])) {
                return;
            }
        }

        throw new LocalizedException(new Phrase('The sortable configuration will lead to infinite loop.'));
    }
}
