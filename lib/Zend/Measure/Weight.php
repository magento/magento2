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
 * @category  Zend
 * @package   Zend_Measure
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Weight.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Implement needed classes
 */
#require_once 'Zend/Measure/Abstract.php';
#require_once 'Zend/Locale.php';

/**
 * Class for handling weight conversions
 *
 * @category   Zend
 * @package    Zend_Measure
 * @subpackage Zend_Measure_Weigth
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Measure_Weight extends Zend_Measure_Abstract
{
    const STANDARD = 'KILOGRAM';

    const ARRATEL                 = 'ARRATEL';
    const ARTEL                   = 'ARTEL';
    const ARROBA_PORTUGUESE       = 'ARROBA_PORTUGUESE';
    const ARROBA                  = 'ARROBA';
    const AS_                     = 'AS_';
    const ASS                     = 'ASS';
    const ATOMIC_MASS_UNIT_1960   = 'ATOMIC_MASS_UNIT_1960';
    const ATOMIC_MASS_UNIT_1973   = 'ATOMIC_MASS_UNIT_1973';
    const ATOMIC_MASS_UNIT_1986   = 'ATOMIC_MASS_UNIT_1986';
    const ATOMIC_MASS_UNIT        = 'ATOMIC_MASS_UNIT';
    const AVOGRAM                 = 'AVOGRAM';
    const BAG                     = 'BAG';
    const BAHT                    = 'BAHT';
    const BALE                    = 'BALE';
    const BALE_US                 = 'BALE_US';
    const BISMAR_POUND            = 'BISMAR_POUND';
    const CANDY                   = 'CANDY';
    const CARAT_INTERNATIONAL     = 'CARAT_INTERNATIONAL';
    const CARAT                   = 'CARAT';
    const CARAT_UK                = 'CARAT_UK';
    const CARAT_US_1913           = 'CARAT_US_1913';
    const CARGA                   = 'CARGA';
    const CATTI                   = 'CATTI';
    const CATTI_JAPANESE          = 'CATTI_JAPANESE';
    const CATTY                   = 'CATTY';
    const CATTY_JAPANESE          = 'CATTY_JAPANESE';
    const CATTY_THAI              = 'CATTY_THAI';
    const CENTAL                  = 'CENTAL';
    const CENTIGRAM               = 'CENTIGRAM';
    const CENTNER                 = 'CENTNER';
    const CENTNER_RUSSIAN         = 'CENTNER_RUSSIAN';
    const CHALDER                 = 'CHALDER';
    const CHALDRON                = 'CHALDRON';
    const CHIN                    = 'CHIN';
    const CHIN_JAPANESE           = 'CHIN_JAPANESE';
    const CLOVE                   = 'CLOVE';
    const CRITH                   = 'CRITH';
    const DALTON                  = 'DALTON';
    const DAN                     = 'DAN';
    const DAN_JAPANESE            = 'DAN_JAPANESE';
    const DECIGRAM                = 'DECIGRAM';
    const DECITONNE               = 'DECITONNE';
    const DEKAGRAM                = 'DEKAGRAM';
    const DEKATONNE               = 'DEKATONNE';
    const DENARO                  = 'DENARO';
    const DENIER                  = 'DENIER';
    const DRACHME                 = 'DRACHME';
    const DRAM                    = 'DRAM';
    const DRAM_APOTHECARIES       = 'DRAM_APOTHECARIES';
    const DYNE                    = 'DYNE';
    const ELECTRON                = 'ELECTRON';
    const ELECTRONVOLT            = 'ELECTRONVOLT';
    const ETTO                    = 'ETTO';
    const EXAGRAM                 = 'EXAGRAM';
    const FEMTOGRAM               = 'FEMTOGRAM';
    const FIRKIN                  = 'FIRKIN';
    const FLASK                   = 'FLASK';
    const FOTHER                  = 'FOTHER';
    const FOTMAL                  = 'FOTMAL';
    const FUNT                    = 'FUNT';
    const FUNTE                   = 'FUNTE';
    const GAMMA                   = 'GAMMA';
    const GIGAELECTRONVOLT        = 'GIGAELECTRONVOLT';
    const GIGAGRAM                = 'GIGAGRAM';
    const GIGATONNE               = 'GIGATONNE';
    const GIN                     = 'GIN';
    const GIN_JAPANESE            = 'GIN_JAPANESE';
    const GRAIN                   = 'GRAIN';
    const GRAM                    = 'GRAM';
    const GRAN                    = 'GRAN';
    const GRANO                   = 'GRANO';
    const GRANI                   = 'GRANI';
    const GROS                    = 'GROS';
    const HECTOGRAM               = 'HECTOGRAM';
    const HUNDRETWEIGHT           = 'HUNDRETWEIGHT';
    const HUNDRETWEIGHT_US        = 'HUNDRETWEIGHT_US';
    const HYL                     = 'HYL';
    const JIN                     = 'JIN';
    const JUPITER                 = 'JUPITER';
    const KATI                    = 'KATI';
    const KATI_JAPANESE           = 'KATI_JAPANESE';
    const KEEL                    = 'KEEL';
    const KEG                     = 'KEG';
    const KILODALTON              = 'KILODALTON';
    const KILOGRAM                = 'KILOGRAM';
    const KILOGRAM_FORCE          = 'KILOGRAM_FORCE';
    const KILOTON                 = 'KILOTON';
    const KILOTON_US              = 'KILOTON_US';
    const KILOTONNE               = 'KILOTONNE';
    const KIN                     = 'KIN';
    const KIP                     = 'KIP';
    const KOYAN                   = 'KOYAN';
    const KWAN                    = 'KWAN';
    const LAST_GERMANY            = 'LAST_GERMANY';
    const LAST                    = 'LAST';
    const LAST_WOOL               = 'LAST_WOOL';
    const LB                      = 'LB';
    const LBS                     = 'LBS';
    const LIANG                   = 'LIANG';
    const LIBRA_ITALIAN           = 'LIBRE_ITALIAN';
    const LIBRA_SPANISH           = 'LIBRA_SPANISH';
    const LIBRA_PORTUGUESE        = 'LIBRA_PORTUGUESE';
    const LIBRA_ANCIENT           = 'LIBRA_ANCIENT';
    const LIBRA                   = 'LIBRA';
    const LIVRE                   = 'LIVRE';
    const LONG_TON                = 'LONG_TON';
    const LOT                     = 'LOT';
    const MACE                    = 'MACE';
    const MAHND                   = 'MAHND';
    const MARC                    = 'MARC';
    const MARCO                   = 'MARCO';
    const MARK                    = 'MARK';
    const MARK_GERMAN             = 'MARK_GERMANY';
    const MAUND                   = 'MAUND';
    const MAUND_PAKISTAN          = 'MAUND_PAKISTAN';
    const MEGADALTON              = 'MEGADALTON';
    const MEGAGRAM                = 'MEGAGRAM';
    const MEGATONNE               = 'MEGATONNE';
    const MERCANTILE_POUND        = 'MERCANTILE_POUND';
    const METRIC_TON              = 'METRIC_TON';
    const MIC                     = 'MIC';
    const MICROGRAM               = 'MICROGRAM';
    const MILLIDALTON             = 'MILLIDALTON';
    const MILLIER                 = 'MILLIER';
    const MILLIGRAM               = 'MILLIGRAM';
    const MILLIMASS_UNIT          = 'MILLIMASS_UNIT';
    const MINA                    = 'MINA';
    const MOMME                   = 'MOMME';
    const MYRIAGRAM               = 'MYRIAGRAM';
    const NANOGRAM                = 'NANOGRAM';
    const NEWTON                  = 'NEWTON';
    const OBOL                    = 'OBOL';
    const OBOLOS                  = 'OBOLOS';
    const OBOLUS                  = 'OBOLUS';
    const OBOLOS_ANCIENT          = 'OBOLOS_ANCIENT';
    const OBOLUS_ANCIENT          = 'OBOLUS_ANCIENT';
    const OKA                     = 'OKA';
    const ONCA                    = 'ONCA';
    const ONCE                    = 'ONCE';
    const ONCIA                   = 'ONCIA';
    const ONZA                    = 'ONZA';
    const ONS                     = 'ONS';
    const OUNCE                   = 'OUNCE';
    const OUNCE_FORCE             = 'OUNCE_FORCE';
    const OUNCE_TROY              = 'OUNCE_TROY';
    const PACKEN                  = 'PACKEN';
    const PENNYWEIGHT             = 'PENNYWEIGHT';
    const PETAGRAM                = 'PETAGRAM';
    const PFUND                   = 'PFUND';
    const PICOGRAM                = 'PICOGRAM';
    const POINT                   = 'POINT';
    const POND                    = 'POND';
    const POUND                   = 'POUND';
    const POUND_FORCE             = 'POUND_FORCE';
    const POUND_METRIC            = 'POUND_METRIC';
    const POUND_TROY              = 'POUND_TROY';
    const PUD                     = 'PUD';
    const POOD                    = 'POOD';
    const PUND                    = 'PUND';
    const QIAN                    = 'QIAN';
    const QINTAR                  = 'QINTAR';
    const QUARTER                 = 'QUARTER';
    const QUARTER_US              = 'QUARTER_US';
    const QUARTER_TON             = 'QUARTER_TON';
    const QUARTERN                = 'QUARTERN';
    const QUARTERN_LOAF           = 'QUARTERN_LOAF';
    const QUINTAL_FRENCH          = 'QUINTAL_FRENCH';
    const QUINTAL                 = 'QUINTAL';
    const QUINTAL_PORTUGUESE      = 'QUINTAL_PORTUGUESE';
    const QUINTAL_SPAIN           = 'QUINTAL_SPAIN';
    const REBAH                   = 'REBAH';
    const ROTL                    = 'ROTL';
    const ROTEL                   = 'ROTEL';
    const ROTTLE                  = 'ROTTLE';
    const RATEL                   = 'RATEL';
    const SACK                    = 'SACK';
    const SCRUPLE                 = 'SCRUPLE';
    const SEER                    = 'SEER';
    const SEER_PAKISTAN           = 'SEER_PAKISTAN';
    const SHEKEL                  = 'SHEKEL';
    const SHORT_TON               = 'SHORT_TON';
    const SLINCH                  = 'SLINCH';
    const SLUG                    = 'SLUG';
    const STONE                   = 'STONE';
    const TAEL                    = 'TAEL';
    const TAHIL_JAPANESE          = 'TAHIL_JAPANESE';
    const TAHIL                   = 'TAHIL';
    const TALENT                  = 'TALENT';
    const TAN                     = 'TAN';
    const TECHNISCHE_MASS_EINHEIT = 'TECHNISCHE_MASS_EINHEIT';
    const TERAGRAM                = 'TERAGRAM';
    const TETRADRACHM             = 'TETRADRACHM';
    const TICAL                   = 'TICAL';
    const TOD                     = 'TOD';
    const TOLA                    = 'TOLA';
    const TOLA_PAKISTAN           = 'TOLA_PAKISTAN';
    const TON_UK                  = 'TON_UK';
    const TON                     = 'TON';
    const TON_US                  = 'TON_US';
    const TONELADA_PORTUGUESE     = 'TONELADA_PORTUGUESE';
    const TONELADA                = 'TONELADA';
    const TONNE                   = 'TONNE';
    const TONNEAU                 = 'TONNEAU';
    const TOVAR                   = 'TOVAR';
    const TROY_OUNCE              = 'TROY_OUNCE';
    const TROY_POUND              = 'TROY_POUND';
    const TRUSS                   = 'TRUSS';
    const UNCIA                   = 'UNCIA';
    const UNZE                    = 'UNZE';
    const VAGON                   = 'VAGON';
    const YOCTOGRAM               = 'YOCTOGRAM';
    const YOTTAGRAM               = 'YOTTAGRAM';
    const ZENTNER                 = 'ZENTNER';
    const ZEPTOGRAM               = 'ZEPTOGRAM';
    const ZETTAGRAM               = 'ZETTAGRAM';

    /**
     * Calculations for all weight units
     *
     * @var array
     */
    protected $_units = array(
        'ARRATEL'               => array('0.5',            'arratel'),
        'ARTEL'                 => array('0.5',            'artel'),
        'ARROBA_PORTUGUESE'     => array('14.69',          'arroba'),
        'ARROBA'                => array('11.502',         '@'),
        'AS_'                   => array('0.000052',       'as'),
        'ASS'                   => array('0.000052',       'ass'),
        'ATOMIC_MASS_UNIT_1960' => array('1.6603145e-27',  'amu'),
        'ATOMIC_MASS_UNIT_1973' => array('1.6605655e-27',  'amu'),
        'ATOMIC_MASS_UNIT_1986' => array('1.6605402e-27',  'amu'),
        'ATOMIC_MASS_UNIT'      => array('1.66053873e-27', 'amu'),
        'AVOGRAM'               => array('1.6605402e-27',  'avogram'),
        'BAG'                   => array('42.63768278',    'bag'),
        'BAHT'                  => array('0.015',          'baht'),
        'BALE'                  => array('326.5865064',    'bl'),
        'BALE_US'               => array('217.7243376',    'bl'),
        'BISMAR_POUND'          => array('5.993',          'bismar pound'),
        'CANDY'                 => array('254',            'candy'),
        'CARAT_INTERNATIONAL'   => array('0.0002',         'ct'),
        'CARAT'                 => array('0.0002',         'ct'),
        'CARAT_UK'              => array('0.00025919564',  'ct'),
        'CARAT_US_1913'         => array('0.0002053',      'ct'),
        'CARGA'                 => array('140',            'carga'),
        'CATTI'                 => array('0.604875',       'catti'),
        'CATTI_JAPANESE'        => array('0.594',          'catti'),
        'CATTY'                 => array('0.5',            'catty'),
        'CATTY_JAPANESE'        => array('0.6',            'catty'),
        'CATTY_THAI'            => array('0.6',            'catty'),
        'CENTAL'                => array('45.359237',      'cH'),
        'CENTIGRAM'             => array('0.00001',        'cg'),
        'CENTNER'               => array('50',             'centner'),
        'CENTNER_RUSSIAN'       => array('100',            'centner'),
        'CHALDER'               => array('2692.52',        'chd'),
        'CHALDRON'              => array('2692.52',        'chd'),
        'CHIN'                  => array('0.5',            'chin'),
        'CHIN_JAPANESE'         => array('0.6',            'chin'),
        'CLOVE'                 => array('3.175',          'clove'),
        'CRITH'                 => array('0.000089885',    'crith'),
        'DALTON'                => array('1.6605402e-27',  'D'),
        'DAN'                   => array('50',             'dan'),
        'DAN_JAPANESE'          => array('60',             'dan'),
        'DECIGRAM'              => array('0.0001',         'dg'),
        'DECITONNE'             => array('100',            'dt'),
        'DEKAGRAM'              => array('0.01',           'dag'),
        'DEKATONNE'             => array('10000',          'dat'),
        'DENARO'                => array('0.0011',         'denaro'),
        'DENIER'                => array('0.001275',       'denier'),
        'DRACHME'               => array('0.0038',         'drachme'),
        'DRAM'                  => array(array('' => '0.45359237', '/' => '256'), 'dr'),
        'DRAM_APOTHECARIES'     => array('0.0038879346',   'dr'),
        'DYNE'                  => array('1.0197162e-6',   'dyn'),
        'ELECTRON'              => array('9.109382e-31',   'e−'),
        'ELECTRONVOLT'          => array('1.782662e-36',   'eV'),
        'ETTO'                  => array('0.1',            'hg'),
        'EXAGRAM'               => array('1.0e+15',        'Eg'),
        'FEMTOGRAM'             => array('1.0e-18',        'fg'),
        'FIRKIN'                => array('25.40117272',    'fir'),
        'FLASK'                 => array('34.7',           'flask'),
        'FOTHER'                => array('979.7595192',    'fother'),
        'FOTMAL'                => array('32.65865064',    'fotmal'),
        'FUNT'                  => array('0.4095',         'funt'),
        'FUNTE'                 => array('0.4095',         'funte'),
        'GAMMA'                 => array('0.000000001',    'gamma'),
        'GIGAELECTRONVOLT'      => array('1.782662e-27',   'GeV'),
        'GIGAGRAM'              => array('1000000',        'Gg'),
        'GIGATONNE'             => array('1.0e+12',        'Gt'),
        'GIN'                   => array('0.6',            'gin'),
        'GIN_JAPANESE'          => array('0.594',          'gin'),
        'GRAIN'                 => array('0.00006479891',  'gr'),
        'GRAM'                  => array('0.001',          'g'),
        'GRAN'                  => array('0.00082',        'gran'),
        'GRANO'                 => array('0.00004905',     'grano'),
        'GRANI'                 => array('0.00004905',     'grani'),
        'GROS'                  => array('0.003824',       'gros'),
        'HECTOGRAM'             => array('0.1',            'hg'),
        'HUNDRETWEIGHT'         => array('50.80234544',    'cwt'),
        'HUNDRETWEIGHT_US'      => array('45.359237',      'cwt'),
        'HYL'                   => array('9.80665',        'hyl'),
        'JIN'                   => array('0.5',            'jin'),
        'JUPITER'               => array('1.899e+27',      'jupiter'),
        'KATI'                  => array('0.5',            'kati'),
        'KATI_JAPANESE'         => array('0.6',            'kati'),
        'KEEL'                  => array('21540.19446656', 'keel'),
        'KEG'                   => array('45.359237',      'keg'),
        'KILODALTON'            => array('1.6605402e-24',  'kD'),
        'KILOGRAM'              => array('1',              'kg'),
        'KILOGRAM_FORCE'        => array('1',              'kgf'),
        'KILOTON'               => array('1016046.9088',   'kt'),
        'KILOTON_US'            => array('907184.74',      'kt'),
        'KILOTONNE'             => array('1000000',        'kt'),
        'KIN'                   => array('0.6',            'kin'),
        'KIP'                   => array('453.59237',      'kip'),
        'KOYAN'                 => array('2419',           'koyan'),
        'KWAN'                  => array('3.75',           'kwan'),
        'LAST_GERMANY'          => array('2000',           'last'),
        'LAST'                  => array('1814.36948',     'last'),
        'LAST_WOOL'             => array('1981.29147216',  'last'),
        'LB'                    => array('0.45359237',     'lb'),
        'LBS'                   => array('0.45359237',     'lbs'),
        'LIANG'                 => array('0.05',           'liang'),
        'LIBRE_ITALIAN'         => array('0.339',          'lb'),
        'LIBRA_SPANISH'         => array('0.459',          'lb'),
        'LIBRA_PORTUGUESE'      => array('0.459',          'lb'),
        'LIBRA_ANCIENT'         => array('0.323',          'lb'),
        'LIBRA'                 => array('1',              'lb'),
        'LIVRE'                 => array('0.4895',         'livre'),
        'LONG_TON'              => array('1016.0469088',   't'),
        'LOT'                   => array('0.015',          'lot'),
        'MACE'                  => array('0.003778',       'mace'),
        'MAHND'                 => array('0.9253284348',   'mahnd'),
        'MARC'                  => array('0.24475',        'marc'),
        'MARCO'                 => array('0.23',           'marco'),
        'MARK'                  => array('0.2268',         'mark'),
        'MARK_GERMANY'          => array('0.2805',         'mark'),
        'MAUND'                 => array('37.3242',        'maund'),
        'MAUND_PAKISTAN'        => array('40',             'maund'),
        'MEGADALTON'            => array('1.6605402e-21',  'MD'),
        'MEGAGRAM'              => array('1000',           'Mg'),
        'MEGATONNE'             => array('1.0e+9',         'Mt'),
        'MERCANTILE_POUND'      => array('0.46655',        'lb merc'),
        'METRIC_TON'            => array('1000',           't'),
        'MIC'                   => array('1.0e-9',         'mic'),
        'MICROGRAM'             => array('1.0e-9',         '�g'),
        'MILLIDALTON'           => array('1.6605402e-30',  'mD'),
        'MILLIER'               => array('1000',           'millier'),
        'MILLIGRAM'             => array('0.000001',       'mg'),
        'MILLIMASS_UNIT'        => array('1.6605402e-30',  'mmu'),
        'MINA'                  => array('0.499',          'mina'),
        'MOMME'                 => array('0.00375',        'momme'),
        'MYRIAGRAM'             => array('10',             'myg'),
        'NANOGRAM'              => array('1.0e-12',        'ng'),
        'NEWTON'                => array('0.101971621',    'N'),
        'OBOL'                  => array('0.0001',         'obol'),
        'OBOLOS'                => array('0.0001',         'obolos'),
        'OBOLUS'                => array('0.0001',         'obolus'),
        'OBOLOS_ANCIENT'        => array('0.0005',         'obolos'),
        'OBOLUS_ANCIENT'        => array('0.00057',        'obolos'),
        'OKA'                   => array('1.28',           'oka'),
        'ONCA'                  => array('0.02869',        'onca'),
        'ONCE'                  => array('0.03059',        'once'),
        'ONCIA'                 => array('0.0273',         'oncia'),
        'ONZA'                  => array('0.02869',        'onza'),
        'ONS'                   => array('0.1',            'ons'),
        'OUNCE'                 => array(array('' => '0.45359237', '/' => '16'),    'oz'),
        'OUNCE_FORCE'           => array(array('' => '0.45359237', '/' => '16'),    'ozf'),
        'OUNCE_TROY'            => array(array('' => '65.31730128', '/' => '2100'), 'oz'),
        'PACKEN'                => array('490.79',         'packen'),
        'PENNYWEIGHT'           => array(array('' => '65.31730128', '/' => '42000'), 'dwt'),
        'PETAGRAM'              => array('1.0e+12',        'Pg'),
        'PFUND'                 => array('0.5',            'pfd'),
        'PICOGRAM'              => array('1.0e-15',        'pg'),
        'POINT'                 => array('0.000002',       'pt'),
        'POND'                  => array('0.5',            'pond'),
        'POUND'                 => array('0.45359237',     'lb'),
        'POUND_FORCE'           => array('0.4535237',      'lbf'),
        'POUND_METRIC'          => array('0.5',            'lb'),
        'POUND_TROY'            => array(array('' => '65.31730128', '/' => '175'), 'lb'),
        'PUD'                   => array('16.3',           'pud'),
        'POOD'                  => array('16.3',           'pood'),
        'PUND'                  => array('0.5',            'pund'),
        'QIAN'                  => array('0.005',          'qian'),
        'QINTAR'                => array('50',             'qintar'),
        'QUARTER'               => array('12.70058636',    'qtr'),
        'QUARTER_US'            => array('11.33980925',    'qtr'),
        'QUARTER_TON'           => array('226.796185',     'qtr'),
        'QUARTERN'              => array('1.587573295',    'quartern'),
        'QUARTERN_LOAF'         => array('1.81436948',     'quartern-loaf'),
        'QUINTAL_FRENCH'        => array('48.95',          'q'),
        'QUINTAL'               => array('100',            'q'),
        'QUINTAL_PORTUGUESE'    => array('58.752',         'q'),
        'QUINTAL_SPAIN'         => array('45.9',           'q'),
        'REBAH'                 => array('0.2855',         'rebah'),
        'ROTL'                  => array('0.5',            'rotl'),
        'ROTEL'                 => array('0.5',            'rotel'),
        'ROTTLE'                => array('0.5',            'rottle'),
        'RATEL'                 => array('0.5',            'ratel'),
        'SACK'                  => array('165.10762268',   'sack'),
        'SCRUPLE'               => array(array('' => '65.31730128', '/' => '50400'), 's'),
        'SEER'                  => array('0.933105',       'seer'),
        'SEER_PAKISTAN'         => array('1',              'seer'),
        'SHEKEL'                => array('0.01142',        'shekel'),
        'SHORT_TON'             => array('907.18474',      'st'),
        'SLINCH'                => array('175.126908',     'slinch'),
        'SLUG'                  => array('14.593903',      'slug'),
        'STONE'                 => array('6.35029318',     'st'),
        'TAEL'                  => array('0.03751',        'tael'),
        'TAHIL_JAPANESE'        => array('0.03751',        'tahil'),
        'TAHIL'                 => array('0.05',           'tahil'),
        'TALENT'                => array('30',             'talent'),
        'TAN'                   => array('50',             'tan'),
        'TECHNISCHE_MASS_EINHEIT' => array('9.80665',      'TME'),
        'TERAGRAM'              => array('1.0e+9',         'Tg'),
        'TETRADRACHM'           => array('0.014',          'tetradrachm'),
        'TICAL'                 => array('0.0164',         'tical'),
        'TOD'                   => array('12.70058636',    'tod'),
        'TOLA'                  => array('0.0116638125',   'tola'),
        'TOLA_PAKISTAN'         => array('0.0125',         'tola'),
        'TON_UK'                => array('1016.0469088',   't'),
        'TON'                   => array('1000',           't'),
        'TON_US'                => array('907.18474',      't'),
        'TONELADA_PORTUGUESE'   => array('793.15',         'tonelada'),
        'TONELADA'              => array('919.9',          'tonelada'),
        'TONNE'                 => array('1000',           't'),
        'TONNEAU'               => array('979',            'tonneau'),
        'TOVAR'                 => array('128.8',          'tovar'),
        'TROY_OUNCE'            => array(array('' => '65.31730128', '/' => '2100'), 'troy oz'),
        'TROY_POUND'            => array(array('' => '65.31730128', '/' => '175'),  'troy lb'),
        'TRUSS'                 => array('25.40117272',    'truss'),
        'UNCIA'                 => array('0.0272875',      'uncia'),
        'UNZE'                  => array('0.03125',        'unze'),
        'VAGON'                 => array('10000',          'vagon'),
        'YOCTOGRAM'             => array('1.0e-27',        'yg'),
        'YOTTAGRAM'             => array('1.0e+21',        'Yg'),
        'ZENTNER'               => array('50',             'Ztr'),
        'ZEPTOGRAM'             => array('1.0e-24',        'zg'),
        'ZETTAGRAM'             => array('1.0e+18',        'Zg'),
        'STANDARD'              => 'KILOGRAM'
    );
}
