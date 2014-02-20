<?php
/**
 * Some function.
 * With long description.
 *
 * @param string|null $inParam
 * @return string
 */
function someFunc($inParam)
{
    if ($inParam === null) {
        $inParam = 'Stranger';
    }
    return 'Hello, ' . $inParam . '!';
}

/**
 * Another function with lot of parameters.
 * With long description.
 *
 * @param string|null $someLongParam
 * @param bool $anotherLongParam
 * @param int $moreEvenLongerParamForAllThatRoutineStuff
 * @param float $andThereGoesOneParameter
 * @return string
 */
function anotherFunc($someLongParam, $anotherLongParam, $moreEvenLongerParamForAllThatRoutineStuff,
    $andThereGoesOneParameter
) {
    if ($someLongParam === null) {
        $inParam = 'Stranger';
    }
    return 'Hello, ' . $someLongParam . '!';
}
