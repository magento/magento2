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
use Magento\Framework\Stdlib\StringUtils;
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

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
     * @var StringUtils
=======
     * @var \Magento\Framework\Stdlib\StringUtils
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    private $stringUtils;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
<<<<<<< HEAD
     * @param StringUtils $stringUtils
=======
     * @param \Magento\Framework\Stdlib\StringUtils $stringUtils
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @param ResultFactory $resultFactory
     * @param int $length
     * @param string $etc
     * @param bool $breakWords
     */
    public function __construct(
<<<<<<< HEAD
        StringUtils $stringUtils,
        ResultFactory $resultFactory,
        int $length = 80,
        string $etc = '...',
        bool $breakWords = true
=======
        \Magento\Framework\Stdlib\StringUtils $stringUtils,
        ResultFactory $resultFactory,
        $length = 80,
        $etc = '...',
        $breakWords = true
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
     * @param mixed $string
=======
     * @param string $string
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            return $result;
        }

        $originalLength = $this->stringUtils->strlen($string);
        if ($originalLength > $length) {
            $length -= $this->stringUtils->strlen($this->etc);
            if ($length <= 0) {
                $result->setValue('');
<<<<<<< HEAD

=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            return $result;
        }

        return $result;
    }
}
