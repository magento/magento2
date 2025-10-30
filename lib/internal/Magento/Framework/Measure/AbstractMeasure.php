<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://framework.zend.com/license
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@zend.com
 * so we can send you a copy immediately.
 *
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (https://www.zend.com/)
 * @license https://framework.zend.com/license New BSD License
 */
declare(strict_types=1);

namespace Magento\Framework\Measure;

use Exception;
use Laminas\I18n\Filter\NumberParse;
use Locale;
use Magento\Framework\Measure\Exception\MeasureException;
use NumberFormatter;

/**
 * Base for Measure
 */
abstract class AbstractMeasure
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected string $type;

    /**
     * @var string|null
     */
    protected ?string $locale = null;

    /**
     * @var array
     */
    protected array $units = [];

    /**
     * @param float|int $value
     * @param string|null $type
     * @param string|null $locale
     * @throws MeasureException
     */
    public function __construct($value, $type = null, $locale = null)
    {
        $this->setLocale($locale);
        $type = $type ?? $this->units['STANDARD'];

        if (empty($this->units[$type])) {
            throw new MeasureException(__(
                'Type (%1) is unknown',
                $type
            ));
        }
        $this->setValue($value, $type, $this->locale);
    }

    /**
     * Returns the conversion list.
     *
     * @return array
     */
    public function getConversionList(): array
    {
        return $this->units;
    }

    /**
     * Sets a new locale for the value representation.
     *
     * @param string|null $locale
     * @return AbstractMeasure
     */
    public function setLocale(?string $locale = null): AbstractMeasure
    {
        $this->locale = $locale ?? Locale::getDefault();

        return $this;
    }

    /**
     * Sets a new locale for the value representation.
     *
     * @param int|string $value
     * @param string $type
     * @param string $locale
     * @return AbstractMeasure
     * @throws MeasureException
     */
    public function setValue($value, string $type, string $locale): AbstractMeasure
    {
        try {
            $numberParse = new NumberParse($locale, NumberFormatter::TYPE_DEFAULT);
            $this->value = (string) $numberParse->filter($value);
            $this->setType($type);
        } catch (Exception $exception) {
            throw new MeasureException(
                __($exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }

        return $this;
    }

    /**
     * Returns the internal value.
     *
     * @param int $round
     * @param string|null $locale
     * @return float|string
     */
    public function getValue(int $round = -1, ?string $locale = null)
    {
        $result = $round < 0 ? $this->value : round((float) $this->value, $round);

        if ($locale !== null) {
            $numberParse = new NumberParse($locale, NumberFormatter::TYPE_DEFAULT);
            return (string) $numberParse->filter($result);
        }

        return $result;
    }

    /**
     * Set a new type, and convert the value.
     *
     * @param string $type
     * @return AbstractMeasure
     * @throws MeasureException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    public function setType(string $type): AbstractMeasure
    {
        if (empty($this->units[$type])) {
            throw new MeasureException(__(
                'Type (%1) is unknown',
                $type
            ));
        }

        if (!empty($this->type)) {
            $value = $this->value;

            if (is_array($this->units[$this->getType()][0])) {
                foreach ($this->units[$this->getType()][0] as $key => $found) {
                    switch ($key) {
                        case "/":
                            if ($found != 0) {
                                $value = $this->div($value, $found, 25);
                            }
                            break;
                        case "+":
                            $value = $this->add($value, $found, 25);
                            break;
                        case "-":
                            $value = $this->sub($value, $found, 25);
                            break;
                        default:
                            $value = $this->mul($value, $found, 25);
                            break;
                    }
                }
            } else {
                $value = $this->mul($value, $this->units[$this->getType()][0], 25);
            }
            if (is_array($this->units[$type][0])) {
                foreach (array_reverse($this->units[$type][0]) as $key => $found) {
                    switch ($key) {
                        case "/":
                            $value = $this->mul($value, $found, 25);
                            break;
                        case "+":
                            $value = $this->sub($value, $found, 25);
                            break;
                        case "-":
                            $value = $this->add($value, $found, 25);
                            break;
                        default:
                            if ($found != 0) {
                                $value = $this->div($value, $found, 25);
                            }
                            break;
                    }
                }
            } else {
                $value = $this->div($value, $this->units[$type][0], 25);
            }
            $valueLength = strlen($value);

            for ($i = 1; $i <= $valueLength; ++$i) {
                if ($value[$valueLength - $i] != '0') {
                    $length = 26 - $i;
                    break;
                }
            }

            $this->value = round((float) $value, $length ?? 0);
        }
        $this->type = $type;

        return $this;
    }

    /**
     * Returns the original type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns a string representation.
     *
     * @param int $round
     * @param string|null $locale
     * @return string
     */
    public function toString(int $round = -1, ?string $locale = null): string
    {
        if ($locale === null) {
            $locale = $this->locale;
        }

        return $this->getValue($round, $locale) . ' ' . $this->units[$this->getType()][1];
    }

    /**
     * Returns a string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * BCAdd - fixes a problem of BCMath and exponential numbers.
     *
     * @param string $firstOption
     * @param string $secondOption
     * @param int|null $scale
     * @return string
     */
    private function add(string $firstOption, string $secondOption, ?int $scale = null): string
    {
        $firstOption = $this->exponent($firstOption, $scale);
        $secondOption = $this->exponent($secondOption, $scale);

        return bcadd($firstOption, $secondOption, $scale);
    }

    /**
     * BCSub - fixes a problem of BCMath and exponential numbers.
     *
     * @param string $firstOption
     * @param string $secondOption
     * @param int|null $scale
     * @return string
     */
    private function sub(string $firstOption, string $secondOption, ?int $scale = null): string
    {
        $firstOption = $this->exponent($firstOption, $scale);
        $secondOption = $this->exponent($secondOption, $scale);

        return bcsub($firstOption, $secondOption, $scale);
    }

    /**
     * BCDiv - fixes a problem of BCMath and exponential numbers.
     *
     * @param string $firstOption
     * @param string $secondOption
     * @param int|null $scale
     * @return string
     */
    private function div(string $firstOption, string $secondOption, ?int $scale = null): string
    {
        $firstOption = $this->exponent($firstOption, $scale);
        $secondOption = $this->exponent($secondOption, $scale);

        return bcdiv($firstOption, $secondOption, $scale);
    }

    /**
     * BCMul - fixes a problem of BCMath and exponential numbers.
     *
     * @param string $firstOption
     * @param string $secondOption
     * @param int|null $scale
     * @return string
     */
    private function mul(string $firstOption, string $secondOption, ?int $scale = null): string
    {
        $firstOption = $this->exponent($firstOption, $scale);
        $secondOption = $this->exponent($secondOption, $scale);

        return bcmul($firstOption, $secondOption, $scale);
    }

    /**
     * Changes exponential numbers to plain string numbers.
     *
     * @param string $value
     * @param int|null $scale
     * @return string
     */
    private function exponent(string $value, ?int $scale = null): string
    {
        if (!extension_loaded('bcmath')) {
            return $value;
        }
        $split = explode('e', $value);

        if (count($split) === 1) {
            $split = explode('E', $value);
        }
        if (count($split) > 1) {
            $value = bcmul($split[0], bcpow('10', $split[1], $scale), $scale);
        }

        return $value;
    }
}
