<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter;

use Magento\Framework\Filter\TruncateFilter\Result;
use Magento\Framework\Filter\TruncateFilter\ResultFactory;
<<<<<<< HEAD
=======
use Magento\Framework\Stdlib\StringUtils;
>>>>>>> upstream/2.2-develop

/**
 * Truncate filter
 *
 * Truncate a string to a certain length if necessary, appending the $etc string.
 * $remainder will contain the string that has been replaced with $etc.
 */
class TruncateFilter implements \Zend_Filter_Interface
{
    /**
     * @var int
     */
    private $length;

    /**
     * @var string
     */
    private $etc;

    /**
     * @var bool
     */
    private $breakWords;

    /**
<<<<<<< HEAD
     * @var \Magento\Framework\Stdlib\StringUtils
=======
     * @var StringUtils
>>>>>>> upstream/2.2-develop
     */
    private $stringUtils;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
<<<<<<< HEAD
     * @param \Magento\Framework\Stdlib\StringUtils $stringUtils
=======
     * @param StringUtils $stringUtils
>>>>>>> upstream/2.2-develop
     * @param ResultFactory $resultFactory
     * @param int $length
     * @param string $etc
     * @param bool $breakWords
     */
    public function __construct(
<<<<<<< HEAD
        \Magento\Framework\Stdlib\StringUtils $stringUtils,
        ResultFactory $resultFactory,
        $length = 80,
        $etc = '...',
        $breakWords = true
=======
        StringUtils $stringUtils,
        ResultFactory $resultFactory,
        int $length = 80,
        string $etc = '...',
        bool $breakWords = true
>>>>>>> upstream/2.2-develop
    ) {
        $this->stringUtils = $stringUtils;
        $this->resultFactory = $resultFactory;
        $this->length = $length;
        $this->etc = $etc;
        $this->breakWords = $breakWords;
    }

    /**
     * Filter value
     *
<<<<<<< HEAD
     * @param string $string
=======
     * @param mixed $string
>>>>>>> upstream/2.2-develop
     * @return Result
     */
    public function filter($string) : Result
    {
        /** @var Result $result */
        $result = $this->resultFactory->create(['value' => $string, 'remainder' => '']);
        $length = $this->length;
        if (0 == $length) {
            $result->setValue('');
<<<<<<< HEAD
=======

>>>>>>> upstream/2.2-develop
            return $result;
        }

        $originalLength = $this->stringUtils->strlen($string);
        if ($originalLength > $length) {
            $length -= $this->stringUtils->strlen($this->etc);
            if ($length <= 0) {
                $result->setValue('');
<<<<<<< HEAD
=======

>>>>>>> upstream/2.2-develop
                return $result;
            }
            $preparedString = $string;
            $preparedLength = $length;
            if (!$this->breakWords) {
                $preparedString = preg_replace(
                    '/\s+?(\S+)?$/u',
                    '',
                    $this->stringUtils->substr($string, 0, $length + 1)
                );
                $preparedLength = $this->stringUtils->strlen($preparedString);
            }
            $result->setRemainder($this->stringUtils->substr($string, $preparedLength, $originalLength));
            $result->setValue($this->stringUtils->substr($preparedString, 0, $length) . $this->etc);
<<<<<<< HEAD
=======

>>>>>>> upstream/2.2-develop
            return $result;
        }

        return $result;
    }
}
