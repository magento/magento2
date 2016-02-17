<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Locale
 * @subpackage Data
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * include needed classes
 */
#require_once 'Zend/Locale.php';

/** @see Zend_Xml_Security */
#require_once 'Zend/Xml/Security.php';

/**
 * Locale data reader, handles the CLDR
 *
 * @category   Zend
 * @package    Zend_Locale
 * @subpackage Data
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Locale_Data
{
    /**
     * Locale files
     *
     * @var array
     */
    private static $_ldml = array();

    /**
     * List of values which are collected
     *
     * @var array
     */
    private static $_list = array();

    /**
     * Internal cache for ldml values
     *
     * @var Zend_Cache_Core
     */
    private static $_cache = null;

    /**
     * Internal value to remember if cache supports tags
     *
     * @var boolean
     */
    private static $_cacheTags = false;

    /**
     * Internal option, cache disabled
     *
     * @var boolean
     */
    private static $_cacheDisabled = false;

    /**
     * Read the content from locale
     *
     * Can be called like:
     * <ldml>
     *     <delimiter>test</delimiter>
     *     <second type='myone'>content</second>
     *     <second type='mysecond'>content2</second>
     *     <third type='mythird' />
     * </ldml>
     *
     * Case 1: _readFile('ar','/ldml/delimiter')             -> returns [] = test
     * Case 1: _readFile('ar','/ldml/second[@type=myone]')   -> returns [] = content
     * Case 2: _readFile('ar','/ldml/second','type')         -> returns [myone] = content; [mysecond] = content2
     * Case 3: _readFile('ar','/ldml/delimiter',,'right')    -> returns [right] = test
     * Case 4: _readFile('ar','/ldml/third','type','myone')  -> returns [myone] = mythird
     *
     * @param  string $locale
     * @param  string $path
     * @param  string $attribute
     * @param  string $value
     * @access private
     * @return array
     */
    private static function _readFile($locale, $path, $attribute, $value, $temp)
    {
        // without attribute - read all values
        // with attribute    - read only this value
        if (!empty(self::$_ldml[(string) $locale])) {

            $result = self::$_ldml[(string) $locale]->xpath($path);
            if (!empty($result)) {
                foreach ($result as &$found) {

                    if (empty($value)) {

                        if (empty($attribute)) {
                            // Case 1
                            $temp[] = (string) $found;
                        } else if (empty($temp[(string) $found[$attribute]])){
                            // Case 2
                            $temp[(string) $found[$attribute]] = (string) $found;
                        }

                    } else if (empty ($temp[$value])) {

                        if (empty($attribute)) {
                            // Case 3
                            $temp[$value] = (string) $found;
                        } else {
                            // Case 4
                            $temp[$value] = (string) $found[$attribute];
                        }

                    }
                }
            }
        }
        return $temp;
    }

    /**
     * Find possible routing to other path or locale
     *
     * @param  string $locale
     * @param  string $path
     * @param  string $attribute
     * @param  string $value
     * @param  array  $temp
     * @return bool
     * @throws Zend_Locale_Exception
     */
    private static function _findRoute($locale, $path, $attribute, $value, &$temp)
    {
        // load locale file if not already in cache
        // needed for alias tag when referring to other locale
        if (empty(self::$_ldml[(string) $locale])) {
            $filename = dirname(__FILE__) . '/Data/' . $locale . '.xml';
            if (!file_exists($filename)) {
                #require_once 'Zend/Locale/Exception.php';
                throw new Zend_Locale_Exception("Missing locale file '$filename' for '$locale' locale.");
            }

            self::$_ldml[(string) $locale] = Zend_Xml_Security::scanFile($filename);
        }

        // search for 'alias' tag in the search path for redirection
        $search = '';
        $tok = strtok($path, '/');

        // parse the complete path
        if (!empty(self::$_ldml[(string) $locale])) {
            while ($tok !== false) {
                $search .=  '/' . $tok;
                if (strpos($search, '[@') !== false) {
                    while (strrpos($search, '[@') > strrpos($search, ']')) {
                        $tok = strtok('/');
                        if (empty($tok)) {
                            $search .= '/';
                        }
                        $search = $search . '/' . $tok;
                    }
                }
                $result = self::$_ldml[(string) $locale]->xpath($search . '/alias');

                // alias found
                if (!empty($result)) {

                    $source = $result[0]['source'];
                    $newpath = $result[0]['path'];

                    // new path - path //ldml is to ignore
                    if ($newpath != '//ldml') {
                        // other path - parse to make real path

                        while (substr($newpath,0,3) == '../') {
                            $newpath = substr($newpath, 3);
                            $search = substr($search, 0, strrpos($search, '/'));
                        }

                        // truncate ../ to realpath otherwise problems with alias
                        $path = $search . '/' . $newpath;
                        while (($tok = strtok('/'))!== false) {
                            $path = $path . '/' . $tok;
                        }
                    }

                    // reroute to other locale
                    if ($source != 'locale') {
                        $locale = $source;
                    }

                    $temp = self::_getFile($locale, $path, $attribute, $value, $temp);
                    return false;
                }

                $tok = strtok('/');
            }
        }
        return true;
    }

    /**
     * Read the right LDML file
     *
     * @param  string      $locale
     * @param  string      $path
     * @param  string|bool $attribute
     * @param  string|bool $value
     * @param  array       $temp
     * @return array
     * @throws Zend_Locale_Exception
     */
    private static function _getFile($locale, $path, $attribute = false, $value = false, $temp = array())
    {
        $result = self::_findRoute($locale, $path, $attribute, $value, $temp);
        if ($result) {
            $temp = self::_readFile($locale, $path, $attribute, $value, $temp);
        }

        // parse required locales reversive
        // example: when given zh_Hans_CN
        // 1. -> zh_Hans_CN
        // 2. -> zh_Hans
        // 3. -> zh
        // 4. -> root
        if (($locale != 'root') && ($result)) {
            // Search for parent locale
            if (false !== strpos($locale, '_')) {
                $parentLocale = self::getContent($locale, 'parentlocale');
                if ($parentLocale) {
                    $temp = self::_getFile($parentLocale, $path, $attribute, $value, $temp);
                }
            }

            $locale = substr($locale, 0, -strlen(strrchr($locale, '_')));
            if (!empty($locale)) {
                $temp = self::_getFile($locale, $path, $attribute, $value, $temp);
            } else {
                $temp = self::_getFile('root', $path, $attribute, $value, $temp);
            }
        }
        return $temp;
    }

    /**
     * Find the details for supplemental calendar datas
     *
     * @param  string $locale Locale for Detaildata
     * @param  array  $list   List to search
     * @return string         Key for Detaildata
     */
    private static function _calendarDetail($locale, $list)
    {
        $ret = "001";
        foreach ($list as $key => $value) {
            if (strpos($locale, '_') !== false) {
                $locale = substr($locale, strpos($locale, '_') + 1);
            }
            if (strpos($key, $locale) !== false) {
                $ret = $key;
                break;
            }
        }
        return $ret;
    }

    /**
     * Internal function for checking the locale
     *
     * @param  string|Zend_Locale $locale Locale to check
     * @return string
     * @throws Zend_Locale_Exception
     */
    private static function _checkLocale($locale)
    {
        if (empty($locale)) {
            $locale = new Zend_Locale();
        }

        if (!(Zend_Locale::isLocale((string) $locale, null, false))) {
            #require_once 'Zend/Locale/Exception.php';
            throw new Zend_Locale_Exception("Locale (" . (string) $locale . ") is a unknown locale");
        }

        if (Zend_Locale::isAlias($locale)) {
            // Return a valid CLDR locale so that the XML file can be loaded.
            return Zend_Locale::getAlias($locale);
        }
        return (string) $locale;
    }

    /**
     * Read the LDML file, get a array of multipath defined value
     *
     * @param  string      $locale
     * @param  string      $path
     * @param  bool|string $value
     * @return array
     * @throws Zend_Locale_Exception
     */
    public static function getList($locale, $path, $value = false)
    {
        $locale = self::_checkLocale($locale);

        if (!isset(self::$_cache) && !self::$_cacheDisabled) {
            #require_once 'Zend/Cache.php';
            self::$_cache = Zend_Cache::factory(
                'Core',
                'File',
                array('automatic_serialization' => true),
                array());
        }

        $val = $value;
        if (is_array($value)) {
            $val = implode('_' , $value);
        }

        $val = urlencode($val);
        $id  = self::_filterCacheId('Zend_LocaleL_' . $locale . '_' . $path . '_' . $val);
        if (!self::$_cacheDisabled && ($result = self::$_cache->load($id))) {
            return unserialize($result);
        }

        $temp = array();
        switch(strtolower($path)) {
            case 'language':
                $temp = self::_getFile($locale, '/ldml/localeDisplayNames/languages/language', 'type');
                break;

            case 'script':
                $temp = self::_getFile($locale, '/ldml/localeDisplayNames/scripts/script', 'type');
                break;

            case 'territory':
                $temp = self::_getFile($locale, '/ldml/localeDisplayNames/territories/territory', 'type');
                if ($value === 1) {
                    foreach($temp as $key => $value) {
                        if ((is_numeric($key) === false) and ($key != 'QO') and ($key != 'EU')) {
                            unset($temp[$key]);
                        }
                    }
                } else if ($value === 2) {
                    foreach($temp as $key => $value) {
                        if (is_numeric($key) or ($key == 'QO') or ($key == 'EU')) {
                            unset($temp[$key]);
                        }
                    }
                }
                break;

            case 'variant':
                $temp = self::_getFile($locale, '/ldml/localeDisplayNames/variants/variant', 'type');
                break;

            case 'key':
                $temp = self::_getFile($locale, '/ldml/localeDisplayNames/keys/key', 'type');
                break;

            case 'type':
                if (empty($value)) {
                    $temp = self::_getFile($locale, '/ldml/localeDisplayNames/types/type', 'type');
                } else {
                    if (($value == 'calendar') or
                        ($value == 'collation') or
                        ($value == 'currency')) {
                        $temp = self::_getFile($locale, '/ldml/localeDisplayNames/types/type[@key=\'' . $value . '\']', 'type');
                    } else {
                        $temp = self::_getFile($locale, '/ldml/localeDisplayNames/types/type[@type=\'' . $value . '\']', 'type');
                    }
                }
                break;

            case 'layout':
                $temp  = self::_getFile($locale, '/ldml/layout/orientation/characterOrder', '', 'characterOrder');
                $temp += self::_getFile($locale, '/ldml/layout/orientation/lineOrder', '', 'lineOrder');
                break;

            case 'contexttransform':
                if (empty($value)) {
                    $value = 'uiListOrMenu';
                }
                $temp = self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'languages\']/contextTransform[@type=\''.$value.'\']', '', 'languages');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'day-format-except-narrow\']/contextTransform[@type=\''.$value.'\']', '', 'day-format-except-narrow');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'day-standalone-except-narrow\']/contextTransform[@type=\''.$value.'\']', '', 'day-standalone-except-narrow');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'day-narrow\']/contextTransform[@type=\''.$value.'\']', '', 'day-narrow');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'month-format-except-narrow\']/contextTransform[@type=\''.$value.'\']', '', 'month-format-except-narrow');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'month-standalone-except-narrow\']/contextTransform[@type=\''.$value.'\']', '', 'month-standalone-except-narrow');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'month-narrow\']/contextTransform[@type=\''.$value.'\']', '', 'month-narrow');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'script\']/contextTransform[@type=\''.$value.'\']', '', 'script');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'territory\']/contextTransform[@type=\''.$value.'\']', '', 'territory');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'variant\']/contextTransform[@type=\''.$value.'\']', '', 'variant');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'key\']/contextTransform[@type=\''.$value.'\']', '', 'key');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'type\']/contextTransform[@type=\''.$value.'\']', '', 'type');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'era-name\']/contextTransform[@type=\''.$value.'\']', '', 'era-name');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'era-abbr\']/contextTransform[@type=\''.$value.'\']', '', 'era-abbr');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'era-narrow\']/contextTransform[@type=\''.$value.'\']', '', 'era-narrow');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'quater-format-wide\']/contextTransform[@type=\''.$value.'\']', '', 'quater-format-wide');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'quater-standalone-wide\']/contextTransform[@type=\''.$value.'\']', '', 'quater-standalone-wide');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'quater-abbreviated\']/contextTransform[@type=\''.$value.'\']', '', 'quater-abbreviated');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'quater-narrow\']/contextTransform[@type=\''.$value.'\']', '', 'quater-narrow');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'calendar-field\']/contextTransform[@type=\''.$value.'\']', '', 'calendar-field');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'symbol\']/contextTransform[@type=\''.$value.'\']', '', 'symbol');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'tense\']/contextTransform[@type=\''.$value.'\']', '', 'tense');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'zone-exemplarCity\']/contextTransform[@type=\''.$value.'\']', '', 'zone-exemplarCity');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'zone-long\']/contextTransform[@type=\''.$value.'\']', '', 'zone-long');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'zone-short\']/contextTransform[@type=\''.$value.'\']', '', 'zone-short');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'metazone-long\']/contextTransform[@type=\''.$value.'\']', '', 'metazone-long');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'metazone-short\']/contextTransform[@type=\''.$value.'\']', '', 'metazone-short');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'displayName-count\']/contextTransform[@type=\''.$value.'\']', '', 'displayName-count');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'displayName\']/contextTransform[@type=\''.$value.'\']', '', 'displayName');
                $temp += self::_getFile($locale, '/ldml/contextTransforms/contextTransformUsage[@type=\'unit-pattern\']/contextTransform[@type=\''.$value.'\']', '', 'unit-pattern');
                break;

            case 'characters':
                $temp  = self::_getFile($locale, '/ldml/characters/exemplarCharacters',                           '', 'characters');
                $temp += self::_getFile($locale, '/ldml/characters/exemplarCharacters[@type=\'auxiliary\']',      '', 'auxiliary');
                // $temp += self::_getFile($locale, '/ldml/characters/exemplarCharacters[@type=\'currencySymbol\']', '', 'currencySymbol');
                break;

            case 'delimiters':
                $temp  = self::_getFile($locale, '/ldml/delimiters/quotationStart',          '', 'quoteStart');
                $temp += self::_getFile($locale, '/ldml/delimiters/quotationEnd',            '', 'quoteEnd');
                $temp += self::_getFile($locale, '/ldml/delimiters/alternateQuotationStart', '', 'quoteStartAlt');
                $temp += self::_getFile($locale, '/ldml/delimiters/alternateQuotationEnd',   '', 'quoteEndAlt');
                break;

            case 'measurement':
                $temp  = self::_getFile('supplementalData', '/supplementalData/measurementData/measurementSystem[@type=\'metric\']', 'territories', 'metric');
                $temp += self::_getFile('supplementalData', '/supplementalData/measurementData/measurementSystem[@type=\'US\']',     'territories', 'US');
                $temp += self::_getFile('supplementalData', '/supplementalData/measurementData/paperSize[@type=\'A4\']',             'territories', 'A4');
                $temp += self::_getFile('supplementalData', '/supplementalData/measurementData/paperSize[@type=\'US-Letter\']',      'territories', 'US-Letter');
                break;

            case 'months':
                if (empty($value)) {
                    $value = "gregorian";
                }
                $temp['context'] = "format";
                $temp['default'] = "wide";
                $temp['format']['abbreviated'] = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'format\']/monthWidth[@type=\'abbreviated\']/month', 'type');
                $temp['format']['narrow']      = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'format\']/monthWidth[@type=\'narrow\']/month', 'type');
                $temp['format']['wide']        = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'format\']/monthWidth[@type=\'wide\']/month', 'type');
                $temp['stand-alone']['abbreviated']  = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'stand-alone\']/monthWidth[@type=\'abbreviated\']/month', 'type');
                $temp['stand-alone']['narrow']       = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'stand-alone\']/monthWidth[@type=\'narrow\']/month', 'type');
                $temp['stand-alone']['wide']         = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'stand-alone\']/monthWidth[@type=\'wide\']/month', 'type');
                break;

            case 'month':
                if (empty($value)) {
                    $value = array("gregorian", "format", "wide");
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/months/monthContext[@type=\'' . $value[1] . '\']/monthWidth[@type=\'' . $value[2] . '\']/month', 'type');
                break;

            case 'days':
                if (empty($value)) {
                    $value = "gregorian";
                }
                $temp['context'] = "format";
                $temp['default'] = "wide";
                $temp['format']['abbreviated'] = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/dayContext[@type=\'format\']/dayWidth[@type=\'abbreviated\']/day', 'type');
                $temp['format']['narrow']      = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/dayContext[@type=\'format\']/dayWidth[@type=\'narrow\']/day', 'type');
                $temp['format']['wide']        = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/dayContext[@type=\'format\']/dayWidth[@type=\'wide\']/day', 'type');
                $temp['stand-alone']['abbreviated']  = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/dayContext[@type=\'stand-alone\']/dayWidth[@type=\'abbreviated\']/day', 'type');
                $temp['stand-alone']['narrow']       = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/dayContext[@type=\'stand-alone\']/dayWidth[@type=\'narrow\']/day', 'type');
                $temp['stand-alone']['wide']         = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/dayContext[@type=\'stand-alone\']/dayWidth[@type=\'wide\']/day', 'type');
                break;

            case 'day':
                if (empty($value)) {
                    $value = array("gregorian", "format", "wide");
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/days/dayContext[@type=\'' . $value[1] . '\']/dayWidth[@type=\'' . $value[2] . '\']/day', 'type');
                break;

            case 'week':
                $minDays   = self::_calendarDetail($locale, self::_getFile('supplementalData', '/supplementalData/weekData/minDays', 'territories'));
                $firstDay  = self::_calendarDetail($locale, self::_getFile('supplementalData', '/supplementalData/weekData/firstDay', 'territories'));
                $weekStart = self::_calendarDetail($locale, self::_getFile('supplementalData', '/supplementalData/weekData/weekendStart', 'territories'));
                $weekEnd   = self::_calendarDetail($locale, self::_getFile('supplementalData', '/supplementalData/weekData/weekendEnd', 'territories'));

                $temp  = self::_getFile('supplementalData', "/supplementalData/weekData/minDays[@territories='" . $minDays . "']", 'count', 'minDays');
                $temp += self::_getFile('supplementalData', "/supplementalData/weekData/firstDay[@territories='" . $firstDay . "']", 'day', 'firstDay');
                $temp += self::_getFile('supplementalData', "/supplementalData/weekData/weekendStart[@territories='" . $weekStart . "']", 'day', 'weekendStart');
                $temp += self::_getFile('supplementalData', "/supplementalData/weekData/weekendEnd[@territories='" . $weekEnd . "']", 'day', 'weekendEnd');
                break;

            case 'quarters':
                if (empty($value)) {
                    $value = "gregorian";
                }
                $temp['format']['abbreviated'] = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/quarters/quarterContext[@type=\'format\']/quarterWidth[@type=\'abbreviated\']/quarter', 'type');
                $temp['format']['narrow']      = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/quarters/quarterContext[@type=\'format\']/quarterWidth[@type=\'narrow\']/quarter', 'type');
                $temp['format']['wide']        = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/quarters/quarterContext[@type=\'format\']/quarterWidth[@type=\'wide\']/quarter', 'type');
                $temp['stand-alone']['abbreviated']  = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/quarters/quarterContext[@type=\'stand-alone\']/quarterWidth[@type=\'abbreviated\']/quarter', 'type');
                $temp['stand-alone']['narrow']       = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/quarters/quarterContext[@type=\'stand-alone\']/quarterWidth[@type=\'narrow\']/quarter', 'type');
                $temp['stand-alone']['wide']         = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/quarters/quarterContext[@type=\'stand-alone\']/quarterWidth[@type=\'wide\']/quarter', 'type');
                break;

            case 'quarter':
                if (empty($value)) {
                    $value = array("gregorian", "format", "wide");
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/quarters/quarterContext[@type=\'' . $value[1] . '\']/quarterWidth[@type=\'' . $value[2] . '\']/quarter', 'type');
                break;

            case 'eras':
                if (empty($value)) {
                    $value = "gregorian";
                }
                $temp['names']       = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/eras/eraNames/era', 'type');
                $temp['abbreviated'] = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/eras/eraAbbr/era', 'type');
                $temp['narrow']      = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/eras/eraNarrow/era', 'type');
                break;

            case 'era':
                if (empty($value)) {
                    $value = array("gregorian", "Abbr");
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/eras/era' . $value[1] . '/era', 'type');
                break;

            case 'date':
                if (empty($value)) {
                    $value = "gregorian";
                }
                $temp  = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'full\']/dateFormat/pattern', '', 'full');
                $temp += self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'long\']/dateFormat/pattern', '', 'long');
                $temp += self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'medium\']/dateFormat/pattern', '', 'medium');
                $temp += self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'short\']/dateFormat/pattern', '', 'short');
                break;

            case 'time':
                if (empty($value)) {
                    $value = "gregorian";
                }
                $temp  = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'full\']/timeFormat/pattern', '', 'full');
                $temp += self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'long\']/timeFormat/pattern', '', 'long');
                $temp += self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'medium\']/timeFormat/pattern', '', 'medium');
                $temp += self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'short\']/timeFormat/pattern', '', 'short');
                break;

            case 'datetime':
                if (empty($value)) {
                    $value = "gregorian";
                }

                $timefull = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'full\']/timeFormat/pattern', '', 'full');
                $timelong = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'long\']/timeFormat/pattern', '', 'long');
                $timemedi = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'medium\']/timeFormat/pattern', '', 'medi');
                $timeshor = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/timeFormatLength[@type=\'short\']/timeFormat/pattern', '', 'shor');

                $datefull = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'full\']/dateFormat/pattern', '', 'full');
                $datelong = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'long\']/dateFormat/pattern', '', 'long');
                $datemedi = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'medium\']/dateFormat/pattern', '', 'medi');
                $dateshor = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/dateFormatLength[@type=\'short\']/dateFormat/pattern', '', 'shor');

                $full = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/dateTimeFormatLength[@type=\'full\']/dateTimeFormat/pattern', '', 'full');
                $long = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/dateTimeFormatLength[@type=\'long\']/dateTimeFormat/pattern', '', 'long');
                $medi = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/dateTimeFormatLength[@type=\'medium\']/dateTimeFormat/pattern', '', 'medi');
                $shor = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/dateTimeFormatLength[@type=\'short\']/dateTimeFormat/pattern', '', 'shor');

                $temp['full']   = str_replace(array('{0}', '{1}'), array($timefull['full'], $datefull['full']), $full['full']);
                $temp['long']   = str_replace(array('{0}', '{1}'), array($timelong['long'], $datelong['long']), $long['long']);
                $temp['medium'] = str_replace(array('{0}', '{1}'), array($timemedi['medi'], $datemedi['medi']), $medi['medi']);
                $temp['short']  = str_replace(array('{0}', '{1}'), array($timeshor['shor'], $dateshor['shor']), $shor['shor']);
                break;

            case 'dateitem':
                if (empty($value)) {
                    $value = "gregorian";
                }
                $_temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/availableFormats/dateFormatItem', 'id');
                foreach($_temp as $key => $found) {
                    $temp += self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/availableFormats/dateFormatItem[@id=\'' . $key . '\']', '', $key);
                }
                break;

            case 'dateinterval':
                if (empty($value)) {
                    $value = "gregorian";
                }
                $_temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/intervalFormats/intervalFormatItem', 'id');
                foreach($_temp as $key => $found) {
                    $temp[$key] = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateTimeFormats/intervalFormats/intervalFormatItem[@id=\'' . $key . '\']/greatestDifference', 'id');
                }
                break;

            case 'field':
                if (empty($value)) {
                    $value = "gregorian";
                }
                $temp2 = self::_getFile($locale, '/ldml/dates/fields/field', 'type');
                // $temp2 = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/fields/field', 'type');
                foreach ($temp2 as $key => $keyvalue) {
                    $temp += self::_getFile($locale, '/ldml/dates/fields/field[@type=\'' . $key . '\']/displayName', '', $key);
                    // $temp += self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/fields/field[@type=\'' . $key . '\']/displayName', '', $key);
                }
                break;

            case 'relative':
                if (empty($value)) {
                    $value = "day";
                }
                $temp = self::_getFile($locale, '/ldml/dates/fields/field[@type=\'' . $value . '\']/relative', 'type');
                break;

            case 'symbols':
                $temp  = self::_getFile($locale, '/ldml/numbers/symbols/decimal',         '', 'decimal');
                $temp += self::_getFile($locale, '/ldml/numbers/symbols/group',           '', 'group');
                $temp += self::_getFile($locale, '/ldml/numbers/symbols/list',            '', 'list');
                $temp += self::_getFile($locale, '/ldml/numbers/symbols/percentSign',     '', 'percent');
                $temp += self::_getFile($locale, '/ldml/numbers/symbols/nativeZeroDigit', '', 'zero');
                $temp += self::_getFile($locale, '/ldml/numbers/symbols/patternDigit',    '', 'pattern');
                $temp += self::_getFile($locale, '/ldml/numbers/symbols/plusSign',        '', 'plus');
                $temp += self::_getFile($locale, '/ldml/numbers/symbols/minusSign',       '', 'minus');
                $temp += self::_getFile($locale, '/ldml/numbers/symbols/exponential',     '', 'exponent');
                $temp += self::_getFile($locale, '/ldml/numbers/symbols/perMille',        '', 'mille');
                $temp += self::_getFile($locale, '/ldml/numbers/symbols/infinity',        '', 'infinity');
                $temp += self::_getFile($locale, '/ldml/numbers/symbols/nan',             '', 'nan');
                break;

            case 'nametocurrency':
                $_temp = self::_getFile($locale, '/ldml/numbers/currencies/currency', 'type');
                foreach ($_temp as $key => $found) {
                    $temp += self::_getFile($locale, '/ldml/numbers/currencies/currency[@type=\'' . $key . '\']/displayName', '', $key);
                }
                break;

            case 'currencytoname':
                $_temp = self::_getFile($locale, '/ldml/numbers/currencies/currency', 'type');
                foreach ($_temp as $key => $keyvalue) {
                    $val = self::_getFile($locale, '/ldml/numbers/currencies/currency[@type=\'' . $key . '\']/displayName', '', $key);
                    if (!isset($val[$key])) {
                        continue;
                    }
                    if (!isset($temp[$val[$key]])) {
                        $temp[$val[$key]] = $key;
                    } else {
                        $temp[$val[$key]] .= " " . $key;
                    }
                }
                break;

            case 'currencysymbol':
                $_temp = self::_getFile($locale, '/ldml/numbers/currencies/currency', 'type');
                foreach ($_temp as $key => $found) {
                    $temp += self::_getFile($locale, '/ldml/numbers/currencies/currency[@type=\'' . $key . '\']/symbol', '', $key);
                }
                break;

            case 'question':
                $temp  = self::_getFile($locale, '/ldml/posix/messages/yesstr',  '', 'yes');
                $temp += self::_getFile($locale, '/ldml/posix/messages/nostr',   '', 'no');
                break;

            case 'currencyfraction':
                $_temp = self::_getFile('supplementalData', '/supplementalData/currencyData/fractions/info', 'iso4217');
                foreach ($_temp as $key => $found) {
                    $temp += self::_getFile('supplementalData', '/supplementalData/currencyData/fractions/info[@iso4217=\'' . $key . '\']', 'digits', $key);
                }
                break;

            case 'currencyrounding':
                $_temp = self::_getFile('supplementalData', '/supplementalData/currencyData/fractions/info', 'iso4217');
                foreach ($_temp as $key => $found) {
                    $temp += self::_getFile('supplementalData', '/supplementalData/currencyData/fractions/info[@iso4217=\'' . $key . '\']', 'rounding', $key);
                }
                break;

            case 'currencytoregion':
                $_temp = self::_getFile('supplementalData', '/supplementalData/currencyData/region', 'iso3166');
                foreach ($_temp as $key => $keyvalue) {
                    $temp += self::_getFile('supplementalData', '/supplementalData/currencyData/region[@iso3166=\'' . $key . '\']/currency', 'iso4217', $key);
                }
                break;

            case 'regiontocurrency':
                $_temp = self::_getFile('supplementalData', '/supplementalData/currencyData/region', 'iso3166');
                foreach ($_temp as $key => $keyvalue) {
                    $val = self::_getFile('supplementalData', '/supplementalData/currencyData/region[@iso3166=\'' . $key . '\']/currency', 'iso4217', $key);
                    if (!isset($val[$key])) {
                        continue;
                    }
                    if (!isset($temp[$val[$key]])) {
                        $temp[$val[$key]] = $key;
                    } else {
                        $temp[$val[$key]] .= " " . $key;
                    }
                }
                break;

            case 'regiontoterritory':
                $_temp = self::_getFile('supplementalData', '/supplementalData/territoryContainment/group', 'type');
                foreach ($_temp as $key => $found) {
                    $temp += self::_getFile('supplementalData', '/supplementalData/territoryContainment/group[@type=\'' . $key . '\']', 'contains', $key);
                }
                break;

            case 'territorytoregion':
                $_temp2 = self::_getFile('supplementalData', '/supplementalData/territoryContainment/group', 'type');
                $_temp = array();
                foreach ($_temp2 as $key => $found) {
                    $_temp += self::_getFile('supplementalData', '/supplementalData/territoryContainment/group[@type=\'' . $key . '\']', 'contains', $key);
                }
                foreach($_temp as $key => $found) {
                    $_temp3 = explode(" ", $found);
                    foreach($_temp3 as $found3) {
                        if (!isset($temp[$found3])) {
                            $temp[$found3] = (string) $key;
                        } else {
                            $temp[$found3] .= " " . $key;
                        }
                    }
                }
                break;

            case 'scripttolanguage':
                $_temp = self::_getFile('supplementalData', '/supplementalData/languageData/language', 'type');
                foreach ($_temp as $key => $found) {
                    $temp += self::_getFile('supplementalData', '/supplementalData/languageData/language[@type=\'' . $key . '\']', 'scripts', $key);
                    if (empty($temp[$key])) {
                        unset($temp[$key]);
                    }
                }
                break;

            case 'languagetoscript':
                $_temp2 = self::_getFile('supplementalData', '/supplementalData/languageData/language', 'type');
                $_temp = array();
                foreach ($_temp2 as $key => $found) {
                    $_temp += self::_getFile('supplementalData', '/supplementalData/languageData/language[@type=\'' . $key . '\']', 'scripts', $key);
                }
                foreach($_temp as $key => $found) {
                    $_temp3 = explode(" ", $found);
                    foreach($_temp3 as $found3) {
                        if (empty($found3)) {
                            continue;
                        }
                        if (!isset($temp[$found3])) {
                            $temp[$found3] = (string) $key;
                        } else {
                            $temp[$found3] .= " " . $key;
                        }
                    }
                }
                break;

            case 'territorytolanguage':
                $_temp = self::_getFile('supplementalData', '/supplementalData/languageData/language', 'type');
                foreach ($_temp as $key => $found) {
                    $temp += self::_getFile('supplementalData', '/supplementalData/languageData/language[@type=\'' . $key . '\']', 'territories', $key);
                    if (empty($temp[$key])) {
                        unset($temp[$key]);
                    }
                }
                break;

            case 'languagetoterritory':
                $_temp2 = self::_getFile('supplementalData', '/supplementalData/languageData/language', 'type');
                $_temp = array();
                foreach ($_temp2 as $key => $found) {
                    $_temp += self::_getFile('supplementalData', '/supplementalData/languageData/language[@type=\'' . $key . '\']', 'territories', $key);
                }
                foreach($_temp as $key => $found) {
                    $_temp3 = explode(" ", $found);
                    foreach($_temp3 as $found3) {
                        if (empty($found3)) {
                            continue;
                        }
                        if (!isset($temp[$found3])) {
                            $temp[$found3] = (string) $key;
                        } else {
                            $temp[$found3] .= " " . $key;
                        }
                    }
                }
                break;

            case 'timezonetowindows':
                $_temp = self::_getFile('windowsZones', '/supplementalData/windowsZones/mapTimezones/mapZone', 'other');
                foreach ($_temp as $key => $found) {
                    $temp += self::_getFile('windowsZones', '/supplementalData/windowsZones/mapTimezones/mapZone[@other=\'' . $key . '\']', 'type', $key);
                }
                break;

            case 'windowstotimezone':
                $_temp = self::_getFile('windowsZones', '/supplementalData/windowsZones/mapTimezones/mapZone', 'type');
                foreach ($_temp as $key => $found) {
                    $temp += self::_getFile('windowsZones', '/supplementalData/windowsZones/mapTimezones/mapZone[@type=\'' .$key . '\']', 'other', $key);
                }
                break;

            case 'territorytotimezone':
                $_temp = self::_getFile('metaZones', '/supplementalData/metaZones/mapTimezones/mapZone', 'type');
                foreach ($_temp as $key => $found) {
                    $temp += self::_getFile('metaZones', '/supplementalData/metaZones/mapTimezones/mapZone[@type=\'' . $key . '\']', 'territory', $key);
                }
                break;

            case 'timezonetoterritory':
                $_temp = self::_getFile('metaZones', '/supplementalData/metaZones/mapTimezones/mapZone', 'territory');
                foreach ($_temp as $key => $found) {
                    $temp += self::_getFile('metaZones', '/supplementalData/metaZones/mapTimezones/mapZone[@territory=\'' . $key . '\']', 'type', $key);
                }
                break;

            case 'citytotimezone':
                $_temp = self::_getFile($locale, '/ldml/dates/timeZoneNames/zone', 'type');
                foreach($_temp as $key => $found) {
                    $temp += self::_getFile($locale, '/ldml/dates/timeZoneNames/zone[@type=\'' . $key . '\']/exemplarCity', '', $key);
                }
                break;

            case 'timezonetocity':
                $_temp  = self::_getFile($locale, '/ldml/dates/timeZoneNames/zone', 'type');
                $temp = array();
                foreach($_temp as $key => $found) {
                    $temp += self::_getFile($locale, '/ldml/dates/timeZoneNames/zone[@type=\'' . $key . '\']/exemplarCity', '', $key);
                    if (!empty($temp[$key])) {
                        $temp[$temp[$key]] = $key;
                    }
                    unset($temp[$key]);
                }
                break;

            case 'phonetoterritory':
                $_temp = self::_getFile('telephoneCodeData', '/supplementalData/telephoneCodeData/codesByTerritory', 'territory');
                foreach ($_temp as $key => $keyvalue) {
                    $temp += self::_getFile('telephoneCodeData', '/supplementalData/telephoneCodeData/codesByTerritory[@territory=\'' . $key . '\']/telephoneCountryCode', 'code', $key);
                }
                break;

            case 'territorytophone':
                $_temp = self::_getFile('telephoneCodeData', '/supplementalData/telephoneCodeData/codesByTerritory', 'territory');
                foreach ($_temp as $key => $keyvalue) {
                    $val = self::_getFile('telephoneCodeData', '/supplementalData/telephoneCodeData/codesByTerritory[@territory=\'' . $key . '\']/telephoneCountryCode', 'code', $key);
                    if (!isset($val[$key])) {
                        continue;
                    }
                    if (!isset($temp[$val[$key]])) {
                        $temp[$val[$key]] = $key;
                    } else {
                        $temp[$val[$key]] .= " " . $key;
                    }
                }
                break;

            case 'numerictoterritory':
                $_temp = self::_getFile('supplementalData', '/supplementalData/codeMappings/territoryCodes', 'type');
                foreach ($_temp as $key => $keyvalue) {
                    $temp += self::_getFile('supplementalData', '/supplementalData/codeMappings/territoryCodes[@type=\'' . $key . '\']', 'numeric', $key);
                }
                break;

            case 'territorytonumeric':
                $_temp = self::_getFile('supplementalData', '/supplementalData/codeMappings/territoryCodes', 'numeric');
                foreach ($_temp as $key => $keyvalue) {
                    $temp += self::_getFile('supplementalData', '/supplementalData/codeMappings/territoryCodes[@numeric=\'' . $key . '\']', 'type', $key);
                }
                break;

            case 'alpha3toterritory':
                $_temp = self::_getFile('supplementalData', '/supplementalData/codeMappings/territoryCodes', 'type');
                foreach ($_temp as $key => $keyvalue) {
                    $temp += self::_getFile('supplementalData', '/supplementalData/codeMappings/territoryCodes[@type=\'' . $key . '\']', 'alpha3', $key);
                }
                break;

            case 'territorytoalpha3':
                $_temp = self::_getFile('supplementalData', '/supplementalData/codeMappings/territoryCodes', 'alpha3');
                foreach ($_temp as $key => $keyvalue) {
                    $temp += self::_getFile('supplementalData', '/supplementalData/codeMappings/territoryCodes[@alpha3=\'' . $key . '\']', 'type', $key);
                }
                break;

            case 'postaltoterritory':
                $_temp = self::_getFile('postalCodeData', '/supplementalData/postalCodeData/postCodeRegex', 'territoryId');
                foreach ($_temp as $key => $keyvalue) {
                    $temp += self::_getFile('postalCodeData', '/supplementalData/postalCodeData/postCodeRegex[@territoryId=\'' . $key . '\']', 'territoryId');
                }
                break;

            case 'numberingsystem':
                $_temp = self::_getFile('numberingSystems', '/supplementalData/numberingSystems/numberingSystem', 'id');
                foreach ($_temp as $key => $keyvalue) {
                    $temp += self::_getFile('numberingSystems', '/supplementalData/numberingSystems/numberingSystem[@id=\'' . $key . '\']', 'digits', $key);
                    if (empty($temp[$key])) {
                        unset($temp[$key]);
                    }
                }
                break;

            case 'chartofallback':
                $_temp = self::_getFile('characters', '/supplementalData/characters/character-fallback/character', 'value');
                foreach ($_temp as $key => $keyvalue) {
                    $temp2 = self::_getFile('characters', '/supplementalData/characters/character-fallback/character[@value=\'' . $key . '\']/substitute', '', $key);
                    $temp[current($temp2)] = $key;
                }
                break;

            case 'fallbacktochar':
                $_temp = self::_getFile('characters', '/supplementalData/characters/character-fallback/character', 'value');
                foreach ($_temp as $key => $keyvalue) {
                    $temp += self::_getFile('characters', '/supplementalData/characters/character-fallback/character[@value=\'' . $key . '\']/substitute', '', $key);
                }
                break;

            case 'localeupgrade':
                $_temp = self::_getFile('likelySubtags', '/supplementalData/likelySubtags/likelySubtag', 'from');
                foreach ($_temp as $key => $keyvalue) {
                    $temp += self::_getFile('likelySubtags', '/supplementalData/likelySubtags/likelySubtag[@from=\'' . $key . '\']', 'to', $key);
                }
                break;

            case 'unit':
                $_temp = self::_getFile($locale, '/ldml/units/unitLength/unit', 'type');
                foreach($_temp as $key => $keyvalue) {
                    $_temp2 = self::_getFile($locale, '/ldml/units/unitLength/unit[@type=\'' . $key . '\']/unitPattern', 'count');
                    $temp[$key] = $_temp2;
                }
                break;

            default :
                #require_once 'Zend/Locale/Exception.php';
                throw new Zend_Locale_Exception("Unknown list ($path) for parsing locale data.");
                break;
        }

        if (isset(self::$_cache)) {
            if (self::$_cacheTags) {
                self::$_cache->save( serialize($temp), $id, array('Zend_Locale'));
            } else {
                self::$_cache->save( serialize($temp), $id);
            }
        }

        return $temp;
    }

    /**
     * Read the LDML file, get a single path defined value
     *
     * @param  string      $locale
     * @param  string      $path
     * @param  bool|string $value
     * @return string
     * @throws Zend_Locale_Exception
     */
    public static function getContent($locale, $path, $value = false)
    {
        $locale = self::_checkLocale($locale);

        if (!isset(self::$_cache) && !self::$_cacheDisabled) {
            #require_once 'Zend/Cache.php';
            self::$_cache = Zend_Cache::factory(
                'Core',
                'File',
                array('automatic_serialization' => true),
                array());
        }

        $val = $value;
        if (is_array($value)) {
            $val = implode('_' , $value);
        }
        $val = urlencode($val);
        $id  = self::_filterCacheId('Zend_LocaleC_' . $locale . '_' . $path . '_' . $val);
        if (!self::$_cacheDisabled && ($result = self::$_cache->load($id))) {
            return unserialize($result);
        }

        switch(strtolower($path)) {
            case 'language':
                $temp = self::_getFile($locale, '/ldml/localeDisplayNames/languages/language[@type=\'' . $value . '\']', 'type');
                break;

            case 'script':
                $temp = self::_getFile($locale, '/ldml/localeDisplayNames/scripts/script[@type=\'' . $value . '\']', 'type');
                break;

            case 'country':
            case 'territory':
                $temp = self::_getFile($locale, '/ldml/localeDisplayNames/territories/territory[@type=\'' . $value . '\']', 'type');
                break;

            case 'variant':
                $temp = self::_getFile($locale, '/ldml/localeDisplayNames/variants/variant[@type=\'' . $value . '\']', 'type');
                break;

            case 'key':
                $temp = self::_getFile($locale, '/ldml/localeDisplayNames/keys/key[@type=\'' . $value . '\']', 'type');
                break;

            case 'defaultcalendar':
                $givenLocale = new Zend_Locale($locale);
                $territory = $givenLocale->getRegion();
                unset($givenLocale);
                $temp = self::_getFile('supplementalData', '/supplementalData/calendarPreferenceData/calendarPreference[contains(@territories,\'' . $territory . '\')]', 'ordering', 'ordering');
                if (isset($temp['ordering'])) {
                    list($temp) = explode(' ', $temp['ordering']);
                } else {
                    $temp = 'gregorian';
                }
                break;

            case 'monthcontext':
                /* default context is always 'format'
                if (empty ($value)) {
                    $value = "gregorian";
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/default', 'choice', 'context');
                */
                $temp = 'format';
                break;

            case 'defaultmonth':
                /* default width is always 'wide'
                if (empty ($value)) {
                    $value = "gregorian";
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/months/monthContext[@type=\'format\']/default', 'choice', 'default');
                */
                $temp = 'wide';
                break;

            case 'month':
                if (!is_array($value)) {
                    $temp = $value;
                    $value = array("gregorian", "format", "wide", $temp);
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/months/monthContext[@type=\'' . $value[1] . '\']/monthWidth[@type=\'' . $value[2] . '\']/month[@type=\'' . $value[3] . '\']', 'type');
                break;

            case 'daycontext':
                /* default context is always 'format'
                if (empty($value)) {
                    $value = "gregorian";
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/default', 'choice', 'context');
                */
                $temp = 'format';
                break;

            case 'defaultday':
                /* default width is always 'wide'
                if (empty($value)) {
                    $value = "gregorian";
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/days/dayContext[@type=\'format\']/default', 'choice', 'default');
                */
                $temp = 'wide';
                break;

            case 'day':
                if (!is_array($value)) {
                    $temp = $value;
                    $value = array("gregorian", "format", "wide", $temp);
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/days/dayContext[@type=\'' . $value[1] . '\']/dayWidth[@type=\'' . $value[2] . '\']/day[@type=\'' . $value[3] . '\']', 'type');
                break;

            case 'quarter':
                if (!is_array($value)) {
                    $temp = $value;
                    $value = array("gregorian", "format", "wide", $temp);
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/quarters/quarterContext[@type=\'' . $value[1] . '\']/quarterWidth[@type=\'' . $value[2] . '\']/quarter[@type=\'' . $value[3] . '\']', 'type');
                break;

            case 'am':
                if (empty($value)) {
                    $value = array("gregorian", "format", "wide");
                }
                if (!is_array($value)) {
                    $temp = $value;
                    $value = array($temp, "format", "wide");
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/dayPeriods/dayPeriodContext[@type=\'' . $value[1] . '\']/dayPeriodWidth[@type=\'' . $value[2] . '\']/dayPeriod[@type=\'am\']', '', 'dayPeriod');
                break;

            case 'pm':
                if (empty($value)) {
                    $value = array("gregorian", "format", "wide");
                }
                if (!is_array($value)) {
                    $temp = $value;
                    $value = array($temp, "format", "wide");
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/dayPeriods/dayPeriodContext[@type=\'' . $value[1] . '\']/dayPeriodWidth[@type=\'' . $value[2] . '\']/dayPeriod[@type=\'pm\']', '', 'dayPeriod');
                break;

            case 'era':
                if (!is_array($value)) {
                    $temp = $value;
                    $value = array("gregorian", "Abbr", $temp);
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/eras/era' . $value[1] . '/era[@type=\'' . $value[2] . '\']', 'type');
                break;

            case 'defaultdate':
                /* default choice is deprecated in CDLR - should be always medium here
                if (empty($value)) {
                    $value = "gregorian";
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/dateFormats/default', 'choice', 'default');
                */
                $temp = 'medium';
                break;

            case 'date':
                if (empty($value)) {
                    $value = array("gregorian", "medium");
                }
                if (!is_array($value)) {
                    $temp = $value;
                    $value = array("gregorian", $temp);
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/dateFormats/dateFormatLength[@type=\'' . $value[1] . '\']/dateFormat/pattern', '', 'pattern');
                break;

            case 'defaulttime':
                /* default choice is deprecated in CDLR - should be always medium here
                if (empty($value)) {
                    $value = "gregorian";
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value . '\']/timeFormats/default', 'choice', 'default');
                */
                $temp = 'medium';
                break;

            case 'time':
                if (empty($value)) {
                    $value = array("gregorian", "medium");
                }
                if (!is_array($value)) {
                    $temp = $value;
                    $value = array("gregorian", $temp);
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/timeFormats/timeFormatLength[@type=\'' . $value[1] . '\']/timeFormat/pattern', '', 'pattern');
                break;

            case 'datetime':
                if (empty($value)) {
                    $value = array("gregorian", "medium");
                }
                if (!is_array($value)) {
                    $temp = $value;
                    $value = array("gregorian", $temp);
                }

                $date     = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/dateFormats/dateFormatLength[@type=\'' . $value[1] . '\']/dateFormat/pattern', '', 'pattern');
                $time     = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/timeFormats/timeFormatLength[@type=\'' . $value[1] . '\']/timeFormat/pattern', '', 'pattern');
                $datetime = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/dateTimeFormats/dateTimeFormatLength[@type=\'' . $value[1] . '\']/dateTimeFormat/pattern', '', 'pattern');
                $temp = str_replace(array('{0}', '{1}'), array(current($time), current($date)), current($datetime));
                break;

            case 'dateitem':
                if (empty($value)) {
                    $value = array("gregorian", "yyMMdd");
                }
                if (!is_array($value)) {
                    $temp = $value;
                    $value = array("gregorian", $temp);
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/dateTimeFormats/availableFormats/dateFormatItem[@id=\'' . $value[1] . '\']', '');
                break;

            case 'dateinterval':
                if (empty($value)) {
                    $value = array("gregorian", "yMd", "y");
                }
                if (!is_array($value)) {
                    $temp = $value;
                    $value = array("gregorian", $temp, $temp[0]);
                }
                $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/dateTimeFormats/intervalFormats/intervalFormatItem[@id=\'' . $value[1] . '\']/greatestDifference[@id=\'' . $value[2] . '\']', '');
                break;

            case 'field':
                if (!is_array($value)) {
                    $temp = $value;
                    $value = array("gregorian", $temp);
                }
                $temp = self::_getFile($locale, '/ldml/dates/fields/field[@type=\'' . $value[1] . '\']/displayName', '', $value[1]);
                break;

            case 'relative':
                if (!is_array($value)) {
                    $temp = $value;
                    $value = array("gregorian", $temp);
                }
                $temp = self::_getFile($locale, '/ldml/dates/fields/field[@type=\'day\']/relative[@type=\'' . $value[1] . '\']', '', $value[1]);
                // $temp = self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\'' . $value[0] . '\']/fields/field/relative[@type=\'' . $value[1] . '\']', '', $value[1]);
                break;

            case 'defaultnumberingsystem':
                $temp = self::_getFile($locale, '/ldml/numbers/defaultNumberingSystem', '', 'default');
                break;

            case 'decimalnumber':
                $temp = self::_getFile($locale, '/ldml/numbers/decimalFormats/decimalFormatLength/decimalFormat/pattern', '', 'default');
                break;

            case 'scientificnumber':
                $temp = self::_getFile($locale, '/ldml/numbers/scientificFormats/scientificFormatLength/scientificFormat/pattern', '', 'default');
                break;

            case 'percentnumber':
                $temp = self::_getFile($locale, '/ldml/numbers/percentFormats/percentFormatLength/percentFormat/pattern', '', 'default');
                break;

            case 'currencynumber':
                $temp = self::_getFile($locale, '/ldml/numbers/currencyFormats/currencyFormatLength/currencyFormat/pattern', '', 'default');
                break;

            case 'nametocurrency':
                $temp = self::_getFile($locale, '/ldml/numbers/currencies/currency[@type=\'' . $value . '\']/displayName', '', $value);
                break;

            case 'currencytoname':
                $temp = self::_getFile($locale, '/ldml/numbers/currencies/currency[@type=\'' . $value . '\']/displayName', '', $value);
                $_temp = self::_getFile($locale, '/ldml/numbers/currencies/currency', 'type');
                $temp = array();
                foreach ($_temp as $key => $keyvalue) {
                    $val = self::_getFile($locale, '/ldml/numbers/currencies/currency[@type=\'' . $key . '\']/displayName', '', $key);
                    if (!isset($val[$key]) or ($val[$key] != $value)) {
                        continue;
                    }
                    if (!isset($temp[$val[$key]])) {
                        $temp[$val[$key]] = $key;
                    } else {
                        $temp[$val[$key]] .= " " . $key;
                    }
                }
                break;

            case 'currencysymbol':
                $temp = self::_getFile($locale, '/ldml/numbers/currencies/currency[@type=\'' . $value . '\']/symbol', '', $value);
                break;

            case 'question':
                $temp = self::_getFile($locale, '/ldml/posix/messages/' . $value . 'str',  '', $value);
                break;

            case 'currencyfraction':
                if (empty($value)) {
                    $value = "DEFAULT";
                }
                $temp = self::_getFile('supplementalData', '/supplementalData/currencyData/fractions/info[@iso4217=\'' . $value . '\']', 'digits', 'digits');
                break;

            case 'currencyrounding':
                if (empty($value)) {
                    $value = "DEFAULT";
                }
                $temp = self::_getFile('supplementalData', '/supplementalData/currencyData/fractions/info[@iso4217=\'' . $value . '\']', 'rounding', 'rounding');
                break;

            case 'currencytoregion':
                $temp = self::_getFile('supplementalData', '/supplementalData/currencyData/region[@iso3166=\'' . $value . '\']/currency', 'iso4217', $value);
                break;

            case 'regiontocurrency':
                $_temp = self::_getFile('supplementalData', '/supplementalData/currencyData/region', 'iso3166');
                $temp = array();
                foreach ($_temp as $key => $keyvalue) {
                    $val = self::_getFile('supplementalData', '/supplementalData/currencyData/region[@iso3166=\'' . $key . '\']/currency', 'iso4217', $key);
                    if (!isset($val[$key]) or ($val[$key] != $value)) {
                        continue;
                    }
                    if (!isset($temp[$val[$key]])) {
                        $temp[$val[$key]] = $key;
                    } else {
                        $temp[$val[$key]] .= " " . $key;
                    }
                }
                break;

            case 'regiontoterritory':
                $temp = self::_getFile('supplementalData', '/supplementalData/territoryContainment/group[@type=\'' . $value . '\']', 'contains', $value);
                break;

            case 'territorytoregion':
                $_temp2 = self::_getFile('supplementalData', '/supplementalData/territoryContainment/group', 'type');
                $_temp = array();
                foreach ($_temp2 as $key => $found) {
                    $_temp += self::_getFile('supplementalData', '/supplementalData/territoryContainment/group[@type=\'' . $key . '\']', 'contains', $key);
                }
                $temp = array();
                foreach($_temp as $key => $found) {
                    $_temp3 = explode(" ", $found);
                    foreach($_temp3 as $found3) {
                        if ($found3 !== $value) {
                            continue;
                        }
                        if (!isset($temp[$found3])) {
                            $temp[$found3] = (string) $key;
                        } else {
                            $temp[$found3] .= " " . $key;
                        }
                    }
                }
                break;

            case 'scripttolanguage':
                $temp = self::_getFile('supplementalData', '/supplementalData/languageData/language[@type=\'' . $value . '\']', 'scripts', $value);
                break;

            case 'languagetoscript':
                $_temp2 = self::_getFile('supplementalData', '/supplementalData/languageData/language', 'type');
                $_temp = array();
                foreach ($_temp2 as $key => $found) {
                    $_temp += self::_getFile('supplementalData', '/supplementalData/languageData/language[@type=\'' . $key . '\']', 'scripts', $key);
                }
                $temp = array();
                foreach($_temp as $key => $found) {
                    $_temp3 = explode(" ", $found);
                    foreach($_temp3 as $found3) {
                        if ($found3 !== $value) {
                            continue;
                        }
                        if (!isset($temp[$found3])) {
                            $temp[$found3] = (string) $key;
                        } else {
                            $temp[$found3] .= " " . $key;
                        }
                    }
                }
                break;

            case 'territorytolanguage':
                $temp = self::_getFile('supplementalData', '/supplementalData/languageData/language[@type=\'' . $value . '\']', 'territories', $value);
                break;

            case 'languagetoterritory':
                $_temp2 = self::_getFile('supplementalData', '/supplementalData/languageData/language', 'type');
                $_temp = array();
                foreach ($_temp2 as $key => $found) {
                    $_temp += self::_getFile('supplementalData', '/supplementalData/languageData/language[@type=\'' . $key . '\']', 'territories', $key);
                }
                $temp = array();
                foreach($_temp as $key => $found) {
                    $_temp3 = explode(" ", $found);
                    foreach($_temp3 as $found3) {
                        if ($found3 !== $value) {
                            continue;
                        }
                        if (!isset($temp[$found3])) {
                            $temp[$found3] = (string) $key;
                        } else {
                            $temp[$found3] .= " " . $key;
                        }
                    }
                }
                break;

            case 'timezonetowindows':
                $temp = self::_getFile('windowsZones', '/supplementalData/windowsZones/mapTimezones/mapZone[@other=\''.$value.'\']', 'type', $value);
                break;

            case 'windowstotimezone':
                $temp = self::_getFile('windowsZones', '/supplementalData/windowsZones/mapTimezones/mapZone[@type=\''.$value.'\']', 'other', $value);
                break;

            case 'territorytotimezone':
                $temp = self::_getFile('metaZones', '/supplementalData/metaZones/mapTimezones/mapZone[@type=\'' . $value . '\']', 'territory', $value);
                break;

            case 'timezonetoterritory':
                $temp = self::_getFile('metaZones', '/supplementalData/metaZones/mapTimezones/mapZone[@territory=\'' . $value . '\']', 'type', $value);
                break;

            case 'citytotimezone':
                $temp = self::_getFile($locale, '/ldml/dates/timeZoneNames/zone[@type=\'' . $value . '\']/exemplarCity', '', $value);
                break;

            case 'timezonetocity':
                $_temp  = self::_getFile($locale, '/ldml/dates/timeZoneNames/zone', 'type');
                $temp = array();
                foreach($_temp as $key => $found) {
                    $temp += self::_getFile($locale, '/ldml/dates/timeZoneNames/zone[@type=\'' . $key . '\']/exemplarCity', '', $key);
                    if (!empty($temp[$key])) {
                        if ($temp[$key] == $value) {
                            $temp[$temp[$key]] = $key;
                        }
                    }
                    unset($temp[$key]);
                }
                break;

            case 'phonetoterritory':
                $temp = self::_getFile('telephoneCodeData', '/supplementalData/telephoneCodeData/codesByTerritory[@territory=\'' . $value . '\']/telephoneCountryCode', 'code', $value);
                break;

            case 'territorytophone':
                $_temp2 = self::_getFile('telephoneCodeData', '/supplementalData/telephoneCodeData/codesByTerritory', 'territory');
                $_temp = array();
                foreach ($_temp2 as $key => $found) {
                    $_temp += self::_getFile('telephoneCodeData', '/supplementalData/telephoneCodeData/codesByTerritory[@territory=\'' . $key . '\']/telephoneCountryCode', 'code', $key);
                }
                $temp = array();
                foreach($_temp as $key => $found) {
                    $_temp3 = explode(" ", $found);
                    foreach($_temp3 as $found3) {
                        if ($found3 !== $value) {
                            continue;
                        }
                        if (!isset($temp[$found3])) {
                            $temp[$found3] = (string) $key;
                        } else {
                            $temp[$found3] .= " " . $key;
                        }
                    }
                }
                break;

            case 'numerictoterritory':
                $temp = self::_getFile('supplementalData', '/supplementalData/codeMappings/territoryCodes[@type=\''.$value.'\']', 'numeric', $value);
                break;

            case 'territorytonumeric':
                $temp = self::_getFile('supplementalData', '/supplementalData/codeMappings/territoryCodes[@numeric=\''.$value.'\']', 'type', $value);
                break;

            case 'alpha3toterritory':
                $temp = self::_getFile('supplementalData', '/supplementalData/codeMappings/territoryCodes[@type=\''.$value.'\']', 'alpha3', $value);
                break;

            case 'territorytoalpha3':
                $temp = self::_getFile('supplementalData', '/supplementalData/codeMappings/territoryCodes[@alpha3=\''.$value.'\']', 'type', $value);
                break;

            case 'postaltoterritory':
                $temp = self::_getFile('postalCodeData', '/supplementalData/postalCodeData/postCodeRegex[@territoryId=\'' . $value . '\']', 'territoryId');
                break;

            case 'numberingsystem':
                $temp = self::_getFile('numberingSystems', '/supplementalData/numberingSystems/numberingSystem[@id=\'' . strtolower($value) . '\']', 'digits', $value);
                break;

            case 'chartofallback':
                $_temp = self::_getFile('characters', '/supplementalData/characters/character-fallback/character', 'value');
                foreach ($_temp as $key => $keyvalue) {
                    $temp2 = self::_getFile('characters', '/supplementalData/characters/character-fallback/character[@value=\'' . $key . '\']/substitute', '', $key);
                    if (current($temp2) == $value) {
                        $temp = $key;
                    }
                }
                break;

                $temp = self::_getFile('characters', '/supplementalData/characters/character-fallback/character[@value=\'' . $value . '\']/substitute', '', $value);
                break;

            case 'fallbacktochar':
                $temp = self::_getFile('characters', '/supplementalData/characters/character-fallback/character[@value=\'' . $value . '\']/substitute', '');
                break;

            case 'localeupgrade':
                $temp = self::_getFile('likelySubtags', '/supplementalData/likelySubtags/likelySubtag[@from=\'' . $value . '\']', 'to', $value);
                break;

            case 'unit':
                $temp = self::_getFile($locale, '/ldml/units/unitLength/unit[@type=\'' . $value[0] . '\']/unitPattern[@count=\'' . $value[1] . '\']', '');
                break;

            case 'parentlocale':
                if (false === $value) {
                    $value = $locale;
                }
                $temp = self::_getFile('supplementalData', "/supplementalData/parentLocales/parentLocale[contains(@locales, '" . $value . "')]", 'parent', 'parent');
                break;

            default :
                #require_once 'Zend/Locale/Exception.php';
                throw new Zend_Locale_Exception("Unknown detail ($path) for parsing locale data.");
                break;
        }

        if (is_array($temp)) {
            $temp = current($temp);
        }
        if (isset(self::$_cache)) {
            if (self::$_cacheTags) {
                self::$_cache->save( serialize($temp), $id, array('Zend_Locale'));
            } else {
                self::$_cache->save( serialize($temp), $id);
            }
        }

        return $temp;
    }

    /**
     * Returns the set cache
     *
     * @return Zend_Cache_Core The set cache
     */
    public static function getCache()
    {
        return self::$_cache;
    }

    /**
     * Set a cache for Zend_Locale_Data
     *
     * @param Zend_Cache_Core $cache A cache frontend
     */
    public static function setCache(Zend_Cache_Core $cache)
    {
        self::$_cache = $cache;
        self::_getTagSupportForCache();
    }

    /**
     * Returns true when a cache is set
     *
     * @return boolean
     */
    public static function hasCache()
    {
        if (self::$_cache !== null) {
            return true;
        }

        return false;
    }

    /**
     * Removes any set cache
     *
     * @return void
     */
    public static function removeCache()
    {
        self::$_cache = null;
    }

    /**
     * Clears all set cache data
     *
     * @return void
     */
    public static function clearCache()
    {
        if (self::$_cacheTags) {
            self::$_cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('Zend_Locale'));
        } else {
            self::$_cache->clean(Zend_Cache::CLEANING_MODE_ALL);
        }
    }

    /**
     * Disables the cache
     *
     * @param bool $flag
     */
    public static function disableCache($flag)
    {
        self::$_cacheDisabled = (boolean) $flag;
    }

    /**
     * Internal method to check if the given cache supports tags
     *
     * @return bool
     */
    private static function _getTagSupportForCache()
    {
        $backend = self::$_cache->getBackend();
        if ($backend instanceof Zend_Cache_Backend_ExtendedInterface) {
            $cacheOptions = $backend->getCapabilities();
            self::$_cacheTags = $cacheOptions['tags'];
        } else {
            self::$_cacheTags = false;
        }

        return self::$_cacheTags;
    }

    /**
     * Filter an ID to only allow valid variable characters
     *
     * @param  string $value
     * @return string
     */
    protected static function _filterCacheId($value)
    {
        return strtr(
            $value,
            array(
                '-' => '_',
                '%' => '_',
                '+' => '_',
                '.' => '_',
            )
        );
    }
}
