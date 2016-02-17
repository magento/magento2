<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

use Zend\Stdlib\StringUtils;

/**
 * Please note there are two standalone test scripts for testing IDN characters due to problems
 * with file encoding.
 *
 * The first is tests/Zend/Validator/HostnameTestStandalone.php which is designed to be run on
 * the command line.
 *
 * The second is tests/Zend/Validator/HostnameTestForm.php which is designed to be run via HTML
 * to allow users to test entering UTF-8 characters in a form.
 */
class Hostname extends AbstractValidator
{
    const CANNOT_DECODE_PUNYCODE  = 'hostnameCannotDecodePunycode';
    const INVALID                 = 'hostnameInvalid';
    const INVALID_DASH            = 'hostnameDashCharacter';
    const INVALID_HOSTNAME        = 'hostnameInvalidHostname';
    const INVALID_HOSTNAME_SCHEMA = 'hostnameInvalidHostnameSchema';
    const INVALID_LOCAL_NAME      = 'hostnameInvalidLocalName';
    const INVALID_URI             = 'hostnameInvalidUri';
    const IP_ADDRESS_NOT_ALLOWED  = 'hostnameIpAddressNotAllowed';
    const LOCAL_NAME_NOT_ALLOWED  = 'hostnameLocalNameNotAllowed';
    const UNDECIPHERABLE_TLD      = 'hostnameUndecipherableTld';
    const UNKNOWN_TLD             = 'hostnameUnknownTld';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::CANNOT_DECODE_PUNYCODE  => "The input appears to be a DNS hostname but the given punycode notation cannot be decoded",
        self::INVALID                 => "Invalid type given. String expected",
        self::INVALID_DASH            => "The input appears to be a DNS hostname but contains a dash in an invalid position",
        self::INVALID_HOSTNAME        => "The input does not match the expected structure for a DNS hostname",
        self::INVALID_HOSTNAME_SCHEMA => "The input appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'",
        self::INVALID_LOCAL_NAME      => "The input does not appear to be a valid local network name",
        self::INVALID_URI             => "The input does not appear to be a valid URI hostname",
        self::IP_ADDRESS_NOT_ALLOWED  => "The input appears to be an IP address, but IP addresses are not allowed",
        self::LOCAL_NAME_NOT_ALLOWED  => "The input appears to be a local network name but local network names are not allowed",
        self::UNDECIPHERABLE_TLD      => "The input appears to be a DNS hostname but cannot extract TLD part",
        self::UNKNOWN_TLD             => "The input appears to be a DNS hostname but cannot match TLD against known list",
    );

    /**
     * @var array
     */
    protected $messageVariables = array(
        'tld' => 'tld',
    );

    const ALLOW_DNS   = 1;  // Allows Internet domain names (e.g., example.com)
    const ALLOW_IP    = 2;  // Allows IP addresses
    const ALLOW_LOCAL = 4;  // Allows local network names (e.g., localhost, www.localdomain)
    const ALLOW_URI   = 8;  // Allows URI hostnames
    const ALLOW_ALL   = 15;  // Allows all types of hostnames

    /**
     * Array of valid top-level-domains
     *
     * @see ftp://data.iana.org/TLD/tlds-alpha-by-domain.txt  List of all TLDs by domain
     * @see http://www.iana.org/domains/root/db/ Official list of supported TLDs
     * @var array
     */
    protected $validTlds = array(
        'abbott',
        'abogado',
        'ac',
        'academy',
        'accountants',
        'active',
        'actor',
        'ad',
        'adult',
        'ae',
        'aero',
        'af',
        'ag',
        'agency',
        'ai',
        'airforce',
        'al',
        'allfinanz',
        'alsace',
        'am',
        'amsterdam',
        'an',
        'android',
        'ao',
        'apartments',
        'aq',
        'aquarelle',
        'ar',
        'archi',
        'army',
        'arpa',
        'as',
        'asia',
        'associates',
        'at',
        'attorney',
        'au',
        'auction',
        'audio',
        'autos',
        'aw',
        'ax',
        'axa',
        'az',
        'ba',
        'band',
        'bank',
        'bar',
        'barclaycard',
        'barclays',
        'bargains',
        'bayern',
        'bb',
        'bd',
        'be',
        'beer',
        'berlin',
        'best',
        'bf',
        'bg',
        'bh',
        'bi',
        'bid',
        'bike',
        'bingo',
        'bio',
        'biz',
        'bj',
        'black',
        'blackfriday',
        'bloomberg',
        'blue',
        'bm',
        'bmw',
        'bn',
        'bnpparibas',
        'bo',
        'boats',
        'boo',
        'boutique',
        'br',
        'brussels',
        'bs',
        'bt',
        'budapest',
        'build',
        'builders',
        'business',
        'buzz',
        'bv',
        'bw',
        'by',
        'bz',
        'bzh',
        'ca',
        'cab',
        'cal',
        'camera',
        'camp',
        'cancerresearch',
        'canon',
        'capetown',
        'capital',
        'caravan',
        'cards',
        'care',
        'career',
        'careers',
        'cartier',
        'casa',
        'cash',
        'casino',
        'cat',
        'catering',
        'cbn',
        'cc',
        'cd',
        'center',
        'ceo',
        'cern',
        'cf',
        'cfd',
        'cg',
        'ch',
        'channel',
        'chat',
        'cheap',
        'chloe',
        'christmas',
        'chrome',
        'church',
        'ci',
        'citic',
        'city',
        'ck',
        'cl',
        'claims',
        'cleaning',
        'click',
        'clinic',
        'clothing',
        'club',
        'cm',
        'cn',
        'co',
        'coach',
        'codes',
        'coffee',
        'college',
        'cologne',
        'com',
        'community',
        'company',
        'computer',
        'condos',
        'construction',
        'consulting',
        'contractors',
        'cooking',
        'cool',
        'coop',
        'country',
        'courses',
        'cr',
        'credit',
        'creditcard',
        'cricket',
        'crs',
        'cruises',
        'cu',
        'cuisinella',
        'cv',
        'cw',
        'cx',
        'cy',
        'cymru',
        'cz',
        'dabur',
        'dad',
        'dance',
        'dating',
        'datsun',
        'day',
        'dclk',
        'de',
        'deals',
        'degree',
        'delivery',
        'democrat',
        'dental',
        'dentist',
        'desi',
        'design',
        'dev',
        'diamonds',
        'diet',
        'digital',
        'direct',
        'directory',
        'discount',
        'dj',
        'dk',
        'dm',
        'dnp',
        'do',
        'docs',
        'domains',
        'doosan',
        'durban',
        'dvag',
        'dz',
        'eat',
        'ec',
        'edu',
        'education',
        'ee',
        'eg',
        'email',
        'emerck',
        'energy',
        'engineer',
        'engineering',
        'enterprises',
        'epson',
        'equipment',
        'er',
        'erni',
        'es',
        'esq',
        'estate',
        'et',
        'eu',
        'eurovision',
        'eus',
        'events',
        'everbank',
        'exchange',
        'expert',
        'exposed',
        'fail',
        'fan',
        'fans',
        'farm',
        'fashion',
        'feedback',
        'fi',
        'finance',
        'financial',
        'firmdale',
        'fish',
        'fishing',
        'fit',
        'fitness',
        'fj',
        'fk',
        'flights',
        'florist',
        'flowers',
        'flsmidth',
        'fly',
        'fm',
        'fo',
        'foo',
        'football',
        'forex',
        'forsale',
        'foundation',
        'fr',
        'frl',
        'frogans',
        'fund',
        'furniture',
        'futbol',
        'ga',
        'gal',
        'gallery',
        'garden',
        'gb',
        'gbiz',
        'gd',
        'gdn',
        'ge',
        'gent',
        'gf',
        'gg',
        'ggee',
        'gh',
        'gi',
        'gift',
        'gifts',
        'gives',
        'gl',
        'glass',
        'gle',
        'global',
        'globo',
        'gm',
        'gmail',
        'gmo',
        'gmx',
        'gn',
        'goldpoint',
        'goo',
        'goog',
        'google',
        'gop',
        'gov',
        'gp',
        'gq',
        'gr',
        'graphics',
        'gratis',
        'green',
        'gripe',
        'gs',
        'gt',
        'gu',
        'guide',
        'guitars',
        'guru',
        'gw',
        'gy',
        'hamburg',
        'hangout',
        'haus',
        'healthcare',
        'help',
        'here',
        'hermes',
        'hiphop',
        'hiv',
        'hk',
        'hm',
        'hn',
        'holdings',
        'holiday',
        'homes',
        'horse',
        'host',
        'hosting',
        'house',
        'how',
        'hr',
        'ht',
        'hu',
        'ibm',
        'id',
        'ie',
        'ifm',
        'il',
        'im',
        'immo',
        'immobilien',
        'in',
        'industries',
        'infiniti',
        'info',
        'ing',
        'ink',
        'institute',
        'insure',
        'int',
        'international',
        'investments',
        'io',
        'iq',
        'ir',
        'irish',
        'is',
        'it',
        'iwc',
        'java',
        'jcb',
        'je',
        'jetzt',
        'jm',
        'jo',
        'jobs',
        'joburg',
        'jp',
        'juegos',
        'kaufen',
        'kddi',
        'ke',
        'kg',
        'kh',
        'ki',
        'kim',
        'kitchen',
        'kiwi',
        'km',
        'kn',
        'koeln',
        'kp',
        'kr',
        'krd',
        'kred',
        'kw',
        'ky',
        'kyoto',
        'kz',
        'la',
        'lacaixa',
        'land',
        'lat',
        'latrobe',
        'lawyer',
        'lb',
        'lc',
        'lds',
        'lease',
        'leclerc',
        'legal',
        'lgbt',
        'li',
        'lidl',
        'life',
        'lighting',
        'limited',
        'limo',
        'link',
        'lk',
        'loans',
        'london',
        'lotte',
        'lotto',
        'lr',
        'ls',
        'lt',
        'ltda',
        'lu',
        'luxe',
        'luxury',
        'lv',
        'ly',
        'ma',
        'madrid',
        'maif',
        'maison',
        'management',
        'mango',
        'market',
        'marketing',
        'markets',
        'marriott',
        'mc',
        'md',
        'me',
        'media',
        'meet',
        'melbourne',
        'meme',
        'memorial',
        'menu',
        'mg',
        'mh',
        'miami',
        'mil',
        'mini',
        'mk',
        'ml',
        'mm',
        'mn',
        'mo',
        'mobi',
        'moda',
        'moe',
        'monash',
        'money',
        'mormon',
        'mortgage',
        'moscow',
        'motorcycles',
        'mov',
        'mp',
        'mq',
        'mr',
        'ms',
        'mt',
        'mtpc',
        'mu',
        'museum',
        'mv',
        'mw',
        'mx',
        'my',
        'mz',
        'na',
        'nagoya',
        'name',
        'navy',
        'nc',
        'ne',
        'net',
        'network',
        'neustar',
        'new',
        'nexus',
        'nf',
        'ng',
        'ngo',
        'nhk',
        'ni',
        'nico',
        'ninja',
        'nissan',
        'nl',
        'no',
        'np',
        'nr',
        'nra',
        'nrw',
        'ntt',
        'nu',
        'nyc',
        'nz',
        'okinawa',
        'om',
        'one',
        'ong',
        'onl',
        'online',
        'ooo',
        'oracle',
        'org',
        'organic',
        'osaka',
        'otsuka',
        'ovh',
        'pa',
        'page',
        'paris',
        'partners',
        'parts',
        'party',
        'pe',
        'pf',
        'pg',
        'ph',
        'pharmacy',
        'photo',
        'photography',
        'photos',
        'physio',
        'piaget',
        'pics',
        'pictet',
        'pictures',
        'pink',
        'pizza',
        'pk',
        'pl',
        'place',
        'plumbing',
        'pm',
        'pn',
        'pohl',
        'poker',
        'porn',
        'post',
        'pr',
        'praxi',
        'press',
        'pro',
        'prod',
        'productions',
        'prof',
        'properties',
        'property',
        'ps',
        'pt',
        'pub',
        'pw',
        'py',
        'qa',
        'qpon',
        'quebec',
        're',
        'realtor',
        'recipes',
        'red',
        'rehab',
        'reise',
        'reisen',
        'reit',
        'ren',
        'rentals',
        'repair',
        'report',
        'republican',
        'rest',
        'restaurant',
        'reviews',
        'rich',
        'rio',
        'rip',
        'ro',
        'rocks',
        'rodeo',
        'rs',
        'rsvp',
        'ru',
        'ruhr',
        'rw',
        'ryukyu',
        'sa',
        'saarland',
        'sale',
        'samsung',
        'sarl',
        'saxo',
        'sb',
        'sc',
        'sca',
        'scb',
        'schmidt',
        'school',
        'schule',
        'schwarz',
        'science',
        'scot',
        'sd',
        'se',
        'services',
        'sew',
        'sexy',
        'sg',
        'sh',
        'shiksha',
        'shoes',
        'shriram',
        'si',
        'singles',
        'site',
        'sj',
        'sk',
        'sky',
        'sl',
        'sm',
        'sn',
        'so',
        'social',
        'software',
        'sohu',
        'solar',
        'solutions',
        'soy',
        'space',
        'spiegel',
        'spreadbetting',
        'sr',
        'st',
        'study',
        'style',
        'su',
        'sucks',
        'supplies',
        'supply',
        'support',
        'surf',
        'surgery',
        'suzuki',
        'sv',
        'sx',
        'sy',
        'sydney',
        'systems',
        'sz',
        'taipei',
        'tatar',
        'tattoo',
        'tax',
        'tc',
        'td',
        'technology',
        'tel',
        'temasek',
        'tennis',
        'tf',
        'tg',
        'th',
        'tienda',
        'tips',
        'tires',
        'tirol',
        'tj',
        'tk',
        'tl',
        'tm',
        'tn',
        'to',
        'today',
        'tokyo',
        'tools',
        'top',
        'toshiba',
        'town',
        'toys',
        'tr',
        'trade',
        'trading',
        'training',
        'travel',
        'trust',
        'tt',
        'tui',
        'tv',
        'tw',
        'tz',
        'ua',
        'ug',
        'uk',
        'university',
        'uno',
        'uol',
        'us',
        'uy',
        'uz',
        'va',
        'vacations',
        'vc',
        've',
        'vegas',
        'ventures',
        'versicherung',
        'vet',
        'vg',
        'vi',
        'viajes',
        'video',
        'villas',
        'vision',
        'vlaanderen',
        'vn',
        'vodka',
        'vote',
        'voting',
        'voto',
        'voyage',
        'vu',
        'wales',
        'wang',
        'watch',
        'webcam',
        'website',
        'wed',
        'wedding',
        'wf',
        'whoswho',
        'wien',
        'wiki',
        'williamhill',
        'wme',
        'work',
        'works',
        'world',
        'ws',
        'wtc',
        'wtf',
        'xin',
        '佛山',
        '集团',
        '在线',
        '한국',
        'ভারত',
        '八卦',
        'موقع',
        '公益',
        '公司',
        '移动',
        '我爱你',
        'москва',
        'қаз',
        'онлайн',
        'сайт',
        'срб',
        'бел',
        '淡马锡',
        'орг',
        '삼성',
        'சிங்கப்பூர்',
        '商标',
        '商店',
        '商城',
        'дети',
        'мкд',
        '中文网',
        '中信',
        '中国',
        '中國',
        '谷歌',
        'భారత్',
        'ලංකා',
        'ભારત',
        'भारत',
        '网店',
        'संगठन',
        '网络',
        'укр',
        '香港',
        '台湾',
        '台灣',
        '手机',
        'мон',
        'الجزائر',
        'عمان',
        'ایران',
        'امارات',
        'بازار',
        'الاردن',
        'بھارت',
        'المغرب',
        'السعودية',
        'مليسيا',
        '政府',
        'شبكة',
        'გე',
        '机构',
        '组织机构',
        'ไทย',
        'سورية',
        'рус',
        'рф',
        'تونس',
        'みんな',
        'グーグル',
        '世界',
        'ਭਾਰਤ',
        '网址',
        '游戏',
        'vermögensberater',
        'vermögensberatung',
        '企业',
        'مصر',
        'قطر',
        '广东',
        'இலங்கை',
        'இந்தியா',
        '新加坡',
        'فلسطين',
        '政务',
        'xxx',
        'xyz',
        'yachts',
        'yandex',
        'ye',
        'yodobashi',
        'yoga',
        'yokohama',
        'youtube',
        'yt',
        'za',
        'zip',
        'zm',
        'zone',
        'zuerich',
        'zw',
    );

    /**
     * Array for valid Idns
     * @see http://www.iana.org/domains/idn-tables/ Official list of supported IDN Chars
     * (.AC) Ascension Island http://www.nic.ac/pdf/AC-IDN-Policy.pdf
     * (.AR) Argentina http://www.nic.ar/faqidn.html
     * (.AS) American Samoa http://www.nic.as/idn/chars.cfm
     * (.AT) Austria http://www.nic.at/en/service/technical_information/idn/charset_converter/
     * (.BIZ) International http://www.iana.org/domains/idn-tables/
     * (.BR) Brazil http://registro.br/faq/faq6.html
     * (.BV) Bouvett Island http://www.norid.no/domeneregistrering/idn/idn_nyetegn.en.html
     * (.CAT) Catalan http://www.iana.org/domains/idn-tables/tables/cat_ca_1.0.html
     * (.CH) Switzerland https://nic.switch.ch/reg/ocView.action?res=EF6GW2JBPVTG67DLNIQXU234MN6SC33JNQQGI7L6#anhang1
     * (.CL) Chile http://www.iana.org/domains/idn-tables/tables/cl_latn_1.0.html
     * (.COM) International http://www.verisign.com/information-services/naming-services/internationalized-domain-names/index.html
     * (.DE) Germany http://www.denic.de/en/domains/idns/liste.html
     * (.DK) Danmark http://www.dk-hostmaster.dk/index.php?id=151
     * (.EE) Estonia https://www.iana.org/domains/idn-tables/tables/pl_et-pl_1.0.html
     * (.ES) Spain https://www.nic.es/media/2008-05/1210147705287.pdf
     * (.FI) Finland http://www.ficora.fi/en/index/palvelut/fiverkkotunnukset/aakkostenkaytto.html
     * (.GR) Greece https://grweb.ics.forth.gr/CharacterTable1_en.jsp
     * (.HU) Hungary http://www.domain.hu/domain/English/szabalyzat/szabalyzat.html
     * (.IL) Israel http://www.isoc.org.il/domains/il-domain-rules.html
     * (.INFO) International http://www.nic.info/info/idn
     * (.IO) British Indian Ocean Territory http://www.nic.io/IO-IDN-Policy.pdf
     * (.IR) Iran http://www.nic.ir/Allowable_Characters_dot-iran
     * (.IS) Iceland http://www.isnic.is/domain/rules.php
     * (.KR) Korea http://www.iana.org/domains/idn-tables/tables/kr_ko-kr_1.0.html
     * (.LI) Liechtenstein https://nic.switch.ch/reg/ocView.action?res=EF6GW2JBPVTG67DLNIQXU234MN6SC33JNQQGI7L6#anhang1
     * (.LT) Lithuania http://www.domreg.lt/static/doc/public/idn_symbols-en.pdf
     * (.MD) Moldova http://www.register.md/
     * (.MUSEUM) International http://www.iana.org/domains/idn-tables/tables/museum_latn_1.0.html
     * (.NET) International http://www.verisign.com/information-services/naming-services/internationalized-domain-names/index.html
     * (.NO) Norway http://www.norid.no/domeneregistrering/idn/idn_nyetegn.en.html
     * (.NU) Niue http://www.worldnames.net/
     * (.ORG) International http://www.pir.org/index.php?db=content/FAQs&tbl=FAQs_Registrant&id=2
     * (.PE) Peru https://www.nic.pe/nuevas_politicas_faq_2.php
     * (.PL) Poland http://www.dns.pl/IDN/allowed_character_sets.pdf
     * (.PR) Puerto Rico http://www.nic.pr/idn_rules.asp
     * (.PT) Portugal https://online.dns.pt/dns_2008/do?com=DS;8216320233;111;+PAGE(4000058)+K-CAT-CODIGO(C.125)+RCNT(100);
     * (.RU) Russia http://www.iana.org/domains/idn-tables/tables/ru_ru-ru_1.0.html
     * (.SA) Saudi Arabia http://www.iana.org/domains/idn-tables/tables/sa_ar_1.0.html
     * (.SE) Sweden http://www.iis.se/english/IDN_campaignsite.shtml?lang=en
     * (.SH) Saint Helena http://www.nic.sh/SH-IDN-Policy.pdf
     * (.SJ) Svalbard and Jan Mayen http://www.norid.no/domeneregistrering/idn/idn_nyetegn.en.html
     * (.TH) Thailand http://www.iana.org/domains/idn-tables/tables/th_th-th_1.0.html
     * (.TM) Turkmenistan http://www.nic.tm/TM-IDN-Policy.pdf
     * (.TR) Turkey https://www.nic.tr/index.php
     * (.UA) Ukraine http://www.iana.org/domains/idn-tables/tables/ua_cyrl_1.2.html
     * (.VE) Venice http://www.iana.org/domains/idn-tables/tables/ve_es_1.0.html
     * (.VN) Vietnam http://www.vnnic.vn/english/5-6-300-2-2-04-20071115.htm#1.%20Introduction
     *
     * @var array
     */
    protected $validIdns = array(
        'AC'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćĉċčďđēėęěĝġģĥħīįĵķĺļľŀłńņňŋőœŕŗřśŝşšţťŧūŭůűųŵŷźżž]{1,63}$/iu'),
        'AR'  => array(1 => '/^[\x{002d}0-9a-zà-ãç-êìíñ-õü]{1,63}$/iu'),
        'AS'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćĉċčďđēĕėęěĝğġģĥħĩīĭįıĵķĸĺļľłńņňŋōŏőœŕŗřśŝşšţťŧũūŭůűųŵŷźż]{1,63}$/iu'),
        'AT'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿœšž]{1,63}$/iu'),
        'BIZ' => 'Hostname/Biz.php',
        'BR'  => array(1 => '/^[\x{002d}0-9a-zà-ãçéíó-õúü]{1,63}$/iu'),
        'BV'  => array(1 => '/^[\x{002d}0-9a-zàáä-éêñ-ôöøüčđńŋšŧž]{1,63}$/iu'),
        'CAT' => array(1 => '/^[\x{002d}0-9a-z·àç-éíïòóúü]{1,63}$/iu'),
        'CH'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿœ]{1,63}$/iu'),
        'CL'  => array(1 => '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu'),
        'CN'  => 'Hostname/Cn.php',
        'COM' => 'Hostname/Com.php',
        'DE'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿăąāćĉčċďđĕěėęēğĝġģĥħĭĩįīıĵķĺľļłńňņŋŏőōœĸŕřŗśŝšşťţŧŭůűũųūŵŷźžż]{1,63}$/iu'),
        'DK'  => array(1 => '/^[\x{002d}0-9a-zäéöü]{1,63}$/iu'),
        'EE'  => array(1 => '/^[\x{002d}0-9a-zäõöüšž]{1,63}$/iu'),
        'ES'  => array(1 => '/^[\x{002d}0-9a-zàáçèéíïñòóúü·]{1,63}$/iu'),
        'EU'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿ]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-zāăąćĉċčďđēĕėęěĝğġģĥħĩīĭįıĵķĺļľŀłńņňŉŋōŏőœŕŗřśŝšťŧũūŭůűųŵŷźżž]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-zșț]{1,63}$/iu',
            4 => '/^[\x{002d}0-9a-zΐάέήίΰαβγδεζηθικλμνξοπρςστυφχψωϊϋόύώ]{1,63}$/iu',
            5 => '/^[\x{002d}0-9a-zабвгдежзийклмнопрстуфхцчшщъыьэюя]{1,63}$/iu',
            6 => '/^[\x{002d}0-9a-zἀ-ἇἐ-ἕἠ-ἧἰ-ἷὀ-ὅὐ-ὗὠ-ὧὰ-ὼώᾀ-ᾇᾐ-ᾗᾠ-ᾧᾰ-ᾴᾶᾷῂῃῄῆῇῐ-ῒΐῖῗῠ-ῧῲῳῴῶῷ]{1,63}$/iu'),
        'FI'  => array(1 => '/^[\x{002d}0-9a-zäåö]{1,63}$/iu'),
        'GR'  => array(1 => '/^[\x{002d}0-9a-zΆΈΉΊΌΎ-ΡΣ-ώἀ-ἕἘ-Ἕἠ-ὅὈ-Ὅὐ-ὗὙὛὝὟ-ώᾀ-ᾴᾶ-ᾼῂῃῄῆ-ῌῐ-ΐῖ-Ίῠ-Ῥῲῳῴῶ-ῼ]{1,63}$/iu'),
        'HK'  => 'Hostname/Cn.php',
        'HU'  => array(1 => '/^[\x{002d}0-9a-záéíóöúüőű]{1,63}$/iu'),
        'IL'  => array(1 => '/^[\x{002d}0-9\x{05D0}-\x{05EA}]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-z]{1,63}$/i'),
        'INFO'=> array(1 => '/^[\x{002d}0-9a-zäåæéöøü]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-záéíóöúüőű]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-záæéíðóöúýþ]{1,63}$/iu',
            4 => '/^[\x{AC00}-\x{D7A3}]{1,17}$/iu',
            5 => '/^[\x{002d}0-9a-zāčēģīķļņōŗšūž]{1,63}$/iu',
            6 => '/^[\x{002d}0-9a-ząčėęįšūųž]{1,63}$/iu',
            7 => '/^[\x{002d}0-9a-zóąćęłńśźż]{1,63}$/iu',
            8 => '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu'),
        'IO'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿăąāćĉčċďđĕěėęēğĝġģĥħĭĩįīıĵķĺľļłńňņŋŏőōœĸŕřŗśŝšşťţŧŭůűũųūŵŷźžż]{1,63}$/iu'),
        'IS'  => array(1 => '/^[\x{002d}0-9a-záéýúíóþæöð]{1,63}$/iu'),
        'IT'  => array(1 => '/^[\x{002d}0-9a-zàâäèéêëìîïòôöùûüæœçÿß-]{1,63}$/iu'),
        'JP'  => 'Hostname/Jp.php',
        'KR'  => array(1 => '/^[\x{AC00}-\x{D7A3}]{1,17}$/iu'),
        'LI'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿœ]{1,63}$/iu'),
        'LT'  => array(1 => '/^[\x{002d}0-9ąčęėįšųūž]{1,63}$/iu'),
        'MD'  => array(1 => '/^[\x{002d}0-9ăâîşţ]{1,63}$/iu'),
        'MUSEUM' => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćċčďđēėęěğġģħīįıķĺļľłńņňŋōőœŕŗřśşšţťŧūůűųŵŷźżžǎǐǒǔ\x{01E5}\x{01E7}\x{01E9}\x{01EF}ə\x{0292}ẁẃẅỳ]{1,63}$/iu'),
        'NET' => 'Hostname/Com.php',
        'NO'  => array(1 => '/^[\x{002d}0-9a-zàáä-éêñ-ôöøüčđńŋšŧž]{1,63}$/iu'),
        'NU'  => 'Hostname/Com.php',
        'ORG' => array(1 => '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-zóąćęłńśźż]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-záäåæéëíðóöøúüýþ]{1,63}$/iu',
            4 => '/^[\x{002d}0-9a-záéíóöúüőű]{1,63}$/iu',
            5 => '/^[\x{002d}0-9a-ząčėęįšūųž]{1,63}$/iu',
            6 => '/^[\x{AC00}-\x{D7A3}]{1,17}$/iu',
            7 => '/^[\x{002d}0-9a-zāčēģīķļņōŗšūž]{1,63}$/iu'),
        'PE'  => array(1 => '/^[\x{002d}0-9a-zñáéíóúü]{1,63}$/iu'),
        'PL'  => array(1 => '/^[\x{002d}0-9a-zāčēģīķļņōŗšūž]{1,63}$/iu',
            2 => '/^[\x{002d}а-ик-ш\x{0450}ѓѕјљњќџ]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-zâîăşţ]{1,63}$/iu',
            4 => '/^[\x{002d}0-9а-яё\x{04C2}]{1,63}$/iu',
            5 => '/^[\x{002d}0-9a-zàáâèéêìíîòóôùúûċġħż]{1,63}$/iu',
            6 => '/^[\x{002d}0-9a-zàäåæéêòóôöøü]{1,63}$/iu',
            7 => '/^[\x{002d}0-9a-zóąćęłńśźż]{1,63}$/iu',
            8 => '/^[\x{002d}0-9a-zàáâãçéêíòóôõúü]{1,63}$/iu',
            9 => '/^[\x{002d}0-9a-zâîăşţ]{1,63}$/iu',
            10=> '/^[\x{002d}0-9a-záäéíóôúýčďĺľňŕšťž]{1,63}$/iu',
            11=> '/^[\x{002d}0-9a-zçë]{1,63}$/iu',
            12=> '/^[\x{002d}0-9а-ик-шђјљњћџ]{1,63}$/iu',
            13=> '/^[\x{002d}0-9a-zćčđšž]{1,63}$/iu',
            14=> '/^[\x{002d}0-9a-zâçöûüğış]{1,63}$/iu',
            15=> '/^[\x{002d}0-9a-záéíñóúü]{1,63}$/iu',
            16=> '/^[\x{002d}0-9a-zäõöüšž]{1,63}$/iu',
            17=> '/^[\x{002d}0-9a-zĉĝĥĵŝŭ]{1,63}$/iu',
            18=> '/^[\x{002d}0-9a-zâäéëîô]{1,63}$/iu',
            19=> '/^[\x{002d}0-9a-zàáâäåæçèéêëìíîïðñòôöøùúûüýćčłńřśš]{1,63}$/iu',
            20=> '/^[\x{002d}0-9a-zäåæõöøüšž]{1,63}$/iu',
            21=> '/^[\x{002d}0-9a-zàáçèéìíòóùú]{1,63}$/iu',
            22=> '/^[\x{002d}0-9a-zàáéíóöúüőű]{1,63}$/iu',
            23=> '/^[\x{002d}0-9ΐά-ώ]{1,63}$/iu',
            24=> '/^[\x{002d}0-9a-zàáâåæçèéêëðóôöøüþœ]{1,63}$/iu',
            25=> '/^[\x{002d}0-9a-záäéíóöúüýčďěňřšťůž]{1,63}$/iu',
            26=> '/^[\x{002d}0-9a-z·àçèéíïòóúü]{1,63}$/iu',
            27=> '/^[\x{002d}0-9а-ъьюя\x{0450}\x{045D}]{1,63}$/iu',
            28=> '/^[\x{002d}0-9а-яёіў]{1,63}$/iu',
            29=> '/^[\x{002d}0-9a-ząčėęįšūųž]{1,63}$/iu',
            30=> '/^[\x{002d}0-9a-záäåæéëíðóöøúüýþ]{1,63}$/iu',
            31=> '/^[\x{002d}0-9a-zàâæçèéêëîïñôùûüÿœ]{1,63}$/iu',
            32=> '/^[\x{002d}0-9а-щъыьэюяёєіїґ]{1,63}$/iu',
            33=> '/^[\x{002d}0-9א-ת]{1,63}$/iu'),
        'PR'  => array(1 => '/^[\x{002d}0-9a-záéíóúñäëïüöâêîôûàèùæçœãõ]{1,63}$/iu'),
        'PT'  => array(1 => '/^[\x{002d}0-9a-záàâãçéêíóôõú]{1,63}$/iu'),
        'RU'  => array(1 => '/^[\x{002d}0-9а-яё]{1,63}$/iu'),
        'SA'  => array(1 => '/^[\x{002d}.0-9\x{0621}-\x{063A}\x{0641}-\x{064A}\x{0660}-\x{0669}]{1,63}$/iu'),
        'SE'  => array(1 => '/^[\x{002d}0-9a-zäåéöü]{1,63}$/iu'),
        'SH'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿăąāćĉčċďđĕěėęēğĝġģĥħĭĩįīıĵķĺľļłńňņŋŏőōœĸŕřŗśŝšşťţŧŭůűũųūŵŷźžż]{1,63}$/iu'),
        'SI'  => array(
            1 => '/^[\x{002d}0-9a-zà-öø-ÿ]{1,63}$/iu',
            2 => '/^[\x{002d}0-9a-zāăąćĉċčďđēĕėęěĝğġģĥħĩīĭįıĵķĺļľŀłńņňŉŋōŏőœŕŗřśŝšťŧũūŭůűųŵŷźżž]{1,63}$/iu',
            3 => '/^[\x{002d}0-9a-zșț]{1,63}$/iu'),
        'SJ'  => array(1 => '/^[\x{002d}0-9a-zàáä-éêñ-ôöøüčđńŋšŧž]{1,63}$/iu'),
        'TH'  => array(1 => '/^[\x{002d}0-9a-z\x{0E01}-\x{0E3A}\x{0E40}-\x{0E4D}\x{0E50}-\x{0E59}]{1,63}$/iu'),
        'TM'  => array(1 => '/^[\x{002d}0-9a-zà-öø-ÿāăąćĉċčďđēėęěĝġģĥħīįĵķĺļľŀłńņňŋőœŕŗřśŝşšţťŧūŭůűųŵŷźżž]{1,63}$/iu'),
        'TW'  => 'Hostname/Cn.php',
        'TR'  => array(1 => '/^[\x{002d}0-9a-zğıüşöç]{1,63}$/iu'),
        'UA'  => array(1 => '/^[\x{002d}0-9a-zабвгдежзийклмнопрстуфхцчшщъыьэюяѐёђѓєѕіїјљњћќѝўџґӂʼ]{1,63}$/iu'),
        'VE'  => array(1 => '/^[\x{002d}0-9a-záéíóúüñ]{1,63}$/iu'),
        'VN'  => array(1 => '/^[ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚÝàáâãèéêìíòóôõùúýĂăĐđĨĩŨũƠơƯư\x{1EA0}-\x{1EF9}]{1,63}$/iu'),
        'мон' => array(1 => '/^[\x{002d}0-9\x{0430}-\x{044F}]{1,63}$/iu'),
        'срб' => array(1 => '/^[\x{002d}0-9а-ик-шђјљњћџ]{1,63}$/iu'),
        'сайт' => array(1 => '/^[\x{002d}0-9а-яёіїѝйўґг]{1,63}$/iu'),
        'онлайн' => array(1 => '/^[\x{002d}0-9а-яёіїѝйўґг]{1,63}$/iu'),
        '中国' => 'Hostname/Cn.php',
        '中國' => 'Hostname/Cn.php',
        'ලංකා' => array(1 => '/^[\x{0d80}-\x{0dff}]{1,63}$/iu'),
        '香港' => 'Hostname/Cn.php',
        '台湾' => 'Hostname/Cn.php',
        '台灣' => 'Hostname/Cn.php',
        'امارات'   => array(1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'),
        'الاردن'    => array(1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'),
        'السعودية' => array(1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'),
        'ไทย' => array(1 => '/^[\x{002d}0-9a-z\x{0E01}-\x{0E3A}\x{0E40}-\x{0E4D}\x{0E50}-\x{0E59}]{1,63}$/iu'),
        'рф' => array(1 => '/^[\x{002d}0-9а-яё]{1,63}$/iu'),
        'تونس' => array(1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'),
        'مصر' => array(1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'),
        'இலங்கை' => array(1 => '/^[\x{0b80}-\x{0bff}]{1,63}$/iu'),
        'فلسطين' => array(1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'),
        'شبكة'  => array(1 => '/^[\x{0621}-\x{0624}\x{0626}-\x{063A}\x{0641}\x{0642}\x{0644}-\x{0648}\x{067E}\x{0686}\x{0698}\x{06A9}\x{06AF}\x{06CC}\x{06F0}-\x{06F9}]{1,30}$/iu'),
    );

    protected $idnLength = array(
        'BIZ' => array(5 => 17, 11 => 15, 12 => 20),
        'CN'  => array(1 => 20),
        'COM' => array(3 => 17, 5 => 20),
        'HK'  => array(1 => 15),
        'INFO'=> array(4 => 17),
        'KR'  => array(1 => 17),
        'NET' => array(3 => 17, 5 => 20),
        'ORG' => array(6 => 17),
        'TW'  => array(1 => 20),
        'امارات' => array(1 => 30),
        'الاردن' => array(1 => 30),
        'السعودية' => array(1 => 30),
        'تونس' => array(1 => 30),
        'مصر' => array(1 => 30),
        'فلسطين' => array(1 => 30),
        'شبكة' => array(1 => 30),
        '中国' => array(1 => 20),
        '中國' => array(1 => 20),
        '香港' => array(1 => 20),
        '台湾' => array(1 => 20),
        '台灣' => array(1 => 20),
    );

    protected $tld;

    /**
     * Options for the hostname validator
     *
     * @var array
     */
    protected $options = array(
        'allow'       => self::ALLOW_DNS, // Allow these hostnames
        'useIdnCheck' => true,  // Check IDN domains
        'useTldCheck' => true,  // Check TLD elements
        'ipValidator' => null,  // IP validator to use
    );

    /**
     * Sets validator options.
     *
     * @param int  $allow       OPTIONAL Set what types of hostname to allow (default ALLOW_DNS)
     * @param bool $useIdnCheck OPTIONAL Set whether IDN domains are validated (default true)
     * @param bool $useTldCheck Set whether the TLD element of a hostname is validated (default true)
     * @param Ip   $ipValidator OPTIONAL
     * @see http://www.iana.org/cctld/specifications-policies-cctlds-01apr02.htm  Technical Specifications for ccTLDs
     */
    public function __construct($options = array())
    {
        if (!is_array($options)) {
            $options = func_get_args();
            $temp['allow'] = array_shift($options);
            if (!empty($options)) {
                $temp['useIdnCheck'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['useTldCheck'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['ipValidator'] = array_shift($options);
            }

            $options = $temp;
        }

        if (!array_key_exists('ipValidator', $options)) {
            $options['ipValidator'] = null;
        }

        parent::__construct($options);
    }

    /**
     * Returns the set ip validator
     *
     * @return Ip
     */
    public function getIpValidator()
    {
        return $this->options['ipValidator'];
    }

    /**
     *
     * @param Ip $ipValidator OPTIONAL
     * @return Hostname;
     */
    public function setIpValidator(Ip $ipValidator = null)
    {
        if ($ipValidator === null) {
            $ipValidator = new Ip();
        }

        $this->options['ipValidator'] = $ipValidator;
        return $this;
    }

    /**
     * Returns the allow option
     *
     * @return int
     */
    public function getAllow()
    {
        return $this->options['allow'];
    }

    /**
     * Sets the allow option
     *
     * @param  int $allow
     * @return Hostname Provides a fluent interface
     */
    public function setAllow($allow)
    {
        $this->options['allow'] = $allow;
        return $this;
    }

    /**
     * Returns the set idn option
     *
     * @return bool
     */
    public function getIdnCheck()
    {
        return $this->options['useIdnCheck'];
    }

    /**
     * Set whether IDN domains are validated
     *
     * This only applies when DNS hostnames are validated
     *
     * @param  bool $useIdnCheck Set to true to validate IDN domains
     * @return Hostname
     */
    public function useIdnCheck($useIdnCheck)
    {
        $this->options['useIdnCheck'] = (bool) $useIdnCheck;
        return $this;
    }

    /**
     * Returns the set tld option
     *
     * @return bool
     */
    public function getTldCheck()
    {
        return $this->options['useTldCheck'];
    }

    /**
     * Set whether the TLD element of a hostname is validated
     *
     * This only applies when DNS hostnames are validated
     *
     * @param  bool $useTldCheck Set to true to validate TLD elements
     * @return Hostname
     */
    public function useTldCheck($useTldCheck)
    {
        $this->options['useTldCheck'] = (bool) $useTldCheck;
        return $this;
    }

    /**
     * Defined by Interface
     *
     * Returns true if and only if the $value is a valid hostname with respect to the current allow option
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);
        // Check input against IP address schema
        if (preg_match('/^[0-9a-f:.]*$/i', $value)
            && $this->getIpValidator()->setTranslator($this->getTranslator())->isValid($value)
        ) {
            if (!($this->getAllow() & self::ALLOW_IP)) {
                $this->error(self::IP_ADDRESS_NOT_ALLOWED);
                return false;
            }

            return true;
        }

        // Local hostnames are allowed to be partial (ending '.')
        if ($this->getAllow() & self::ALLOW_LOCAL) {
            if (substr($value, -1) === '.') {
                $value = substr($value, 0, -1);
                if (substr($value, -1) === '.') {
                    // Empty hostnames (ending '..') are not allowed
                    $this->error(self::INVALID_LOCAL_NAME);
                    return false;
                }
            }
        }

        $domainParts = explode('.', $value);

        // Prevent partial IP V4 addresses (ending '.')
        if (count($domainParts) == 4 && preg_match('/^[0-9.a-e:.]*$/i', $value)
            && $this->getIpValidator()->setTranslator($this->getTranslator())->isValid($value)
        ) {
            $this->error(self::INVALID_LOCAL_NAME);
        }

        $utf8StrWrapper = StringUtils::getWrapper('UTF-8');

        // Check input against DNS hostname schema
        if (count($domainParts) > 1
            && $utf8StrWrapper->strlen($value) >= 4
            && $utf8StrWrapper->strlen($value) <= 254
        ) {
            $status = false;

            do {
                // First check TLD
                $matches = array();
                if (preg_match('/([^.]{2,63})$/iu', end($domainParts), $matches)
                    || (array_key_exists(end($domainParts), $this->validIdns))
                ) {
                    reset($domainParts);

                    // Hostname characters are: *(label dot)(label dot label); max 254 chars
                    // label: id-prefix [*ldh{61} id-prefix]; max 63 chars
                    // id-prefix: alpha / digit
                    // ldh: alpha / digit / dash

                    // Match TLD against known list
                    $this->tld = strtoupper($matches[1]);
                    if ($this->getTldCheck()) {
                        if (!in_array(strtolower($this->tld), $this->validTlds)
                            && !in_array($this->tld, $this->validTlds)) {
                            $this->error(self::UNKNOWN_TLD);
                            $status = false;
                            break;
                        }
                        // We have already validated that the TLD is fine. We don't want it to go through the below
                        // checks as new UTF-8 TLDs will incorrectly fail if there is no IDN regex for it.
                        array_pop($domainParts);
                    }

                    /**
                     * Match against IDN hostnames
                     * Note: Keep label regex short to avoid issues with long patterns when matching IDN hostnames
                     *
                     * @see Hostname\Interface
                     */
                    $regexChars = array(0 => '/^[a-z0-9\x2d]{1,63}$/i');
                    if ($this->getIdnCheck() && isset($this->validIdns[$this->tld])) {
                        if (is_string($this->validIdns[$this->tld])) {
                            $regexChars += include __DIR__ . '/' . $this->validIdns[$this->tld];
                        } else {
                            $regexChars += $this->validIdns[$this->tld];
                        }
                    }

                    // Check each hostname part
                    $check = 0;
                    foreach ($domainParts as $domainPart) {
                        // Decode Punycode domain names to IDN
                        if (strpos($domainPart, 'xn--') === 0) {
                            $domainPart = $this->decodePunycode(substr($domainPart, 4));
                            if ($domainPart === false) {
                                return false;
                            }
                        }

                        // Check dash (-) does not start, end or appear in 3rd and 4th positions
                        if ($utf8StrWrapper->strpos($domainPart, '-') === 0
                            || ($utf8StrWrapper->strlen($domainPart) > 2
                                && $utf8StrWrapper->strpos($domainPart, '-', 2) == 2
                                && $utf8StrWrapper->strpos($domainPart, '-', 3) == 3
                            )
                            || ($utf8StrWrapper->strpos($domainPart, '-') === ($utf8StrWrapper->strlen($domainPart) - 1))
                        ) {
                            $this->error(self::INVALID_DASH);
                            $status = false;
                            break 2;
                        }

                        // Check each domain part
                        $checked = false;
                        foreach ($regexChars as $regexKey => $regexChar) {
                            $status = preg_match($regexChar, $domainPart);
                            if ($status > 0) {
                                $length = 63;
                                if (array_key_exists($this->tld, $this->idnLength)
                                    && array_key_exists($regexKey, $this->idnLength[$this->tld])
                                ) {
                                    $length = $this->idnLength[$this->tld];
                                }

                                if ($utf8StrWrapper->strlen($domainPart) > $length) {
                                    $this->error(self::INVALID_HOSTNAME);
                                    $status = false;
                                } else {
                                    $checked = true;
                                    break;
                                }
                            }
                        }

                        if ($checked) {
                            ++$check;
                        }
                    }

                    // If one of the labels doesn't match, the hostname is invalid
                    if ($check !== count($domainParts)) {
                        $this->error(self::INVALID_HOSTNAME_SCHEMA);
                        $status = false;
                    }
                } else {
                    // Hostname not long enough
                    $this->error(self::UNDECIPHERABLE_TLD);
                    $status = false;
                }
            } while (false);

            // If the input passes as an Internet domain name, and domain names are allowed, then the hostname
            // passes validation
            if ($status && ($this->getAllow() & self::ALLOW_DNS)) {
                return true;
            }
        } elseif ($this->getAllow() & self::ALLOW_DNS) {
            $this->error(self::INVALID_HOSTNAME);
        }

        // Check for URI Syntax (RFC3986)
        if ($this->getAllow() & self::ALLOW_URI) {
            if (preg_match("/^([a-zA-Z0-9-._~!$&\'()*+,;=]|%[[:xdigit:]]{2}){1,254}$/i", $value)) {
                return true;
            }

            $this->error(self::INVALID_URI);
        }

        // Check input against local network name schema; last chance to pass validation
        $regexLocal = '/^(([a-zA-Z0-9\x2d]{1,63}\x2e)*[a-zA-Z0-9\x2d]{1,63}[\x2e]{0,1}){1,254}$/';
        $status = preg_match($regexLocal, $value);

        // If the input passes as a local network name, and local network names are allowed, then the
        // hostname passes validation
        $allowLocal = $this->getAllow() & self::ALLOW_LOCAL;
        if ($status && $allowLocal) {
            return true;
        }

        // If the input does not pass as a local network name, add a message
        if (!$status) {
            $this->error(self::INVALID_LOCAL_NAME);
        }

        // If local network names are not allowed, add a message
        if ($status && !$allowLocal) {
            $this->error(self::LOCAL_NAME_NOT_ALLOWED);
        }

        return false;
    }

    /**
     * Decodes a punycode encoded string to it's original utf8 string
     * Returns false in case of a decoding failure.
     *
     * @param  string $encoded Punycode encoded string to decode
     * @return string|false
     */
    protected function decodePunycode($encoded)
    {
        if (!preg_match('/^[a-z0-9-]+$/i', $encoded)) {
            // no punycode encoded string
            $this->error(self::CANNOT_DECODE_PUNYCODE);
            return false;
        }

        $decoded = array();
        $separator = strrpos($encoded, '-');
        if ($separator > 0) {
            for ($x = 0; $x < $separator; ++$x) {
                // prepare decoding matrix
                $decoded[] = ord($encoded[$x]);
            }
        }

        $lengthd = count($decoded);
        $lengthe = strlen($encoded);

        // decoding
        $init  = true;
        $base  = 72;
        $index = 0;
        $char  = 0x80;

        for ($indexe = ($separator) ? ($separator + 1) : 0; $indexe < $lengthe; ++$lengthd) {
            for ($oldIndex = $index, $pos = 1, $key = 36; 1; $key += 36) {
                $hex   = ord($encoded[$indexe++]);
                $digit = ($hex - 48 < 10) ? $hex - 22
                       : (($hex - 65 < 26) ? $hex - 65
                       : (($hex - 97 < 26) ? $hex - 97
                       : 36));

                $index += $digit * $pos;
                $tag    = ($key <= $base) ? 1 : (($key >= $base + 26) ? 26 : ($key - $base));
                if ($digit < $tag) {
                    break;
                }

                $pos = (int) ($pos * (36 - $tag));
            }

            $delta   = intval($init ? (($index - $oldIndex) / 700) : (($index - $oldIndex) / 2));
            $delta  += intval($delta / ($lengthd + 1));
            for ($key = 0; $delta > 910 / 2; $key += 36) {
                $delta = intval($delta / 35);
            }

            $base   = intval($key + 36 * $delta / ($delta + 38));
            $init   = false;
            $char  += (int) ($index / ($lengthd + 1));
            $index %= ($lengthd + 1);
            if ($lengthd > 0) {
                for ($i = $lengthd; $i > $index; $i--) {
                    $decoded[$i] = $decoded[($i - 1)];
                }
            }

            $decoded[$index++] = $char;
        }

        // convert decoded ucs4 to utf8 string
        foreach ($decoded as $key => $value) {
            if ($value < 128) {
                $decoded[$key] = chr($value);
            } elseif ($value < (1 << 11)) {
                $decoded[$key]  = chr(192 + ($value >> 6));
                $decoded[$key] .= chr(128 + ($value & 63));
            } elseif ($value < (1 << 16)) {
                $decoded[$key]  = chr(224 + ($value >> 12));
                $decoded[$key] .= chr(128 + (($value >> 6) & 63));
                $decoded[$key] .= chr(128 + ($value & 63));
            } elseif ($value < (1 << 21)) {
                $decoded[$key]  = chr(240 + ($value >> 18));
                $decoded[$key] .= chr(128 + (($value >> 12) & 63));
                $decoded[$key] .= chr(128 + (($value >> 6) & 63));
                $decoded[$key] .= chr(128 + ($value & 63));
            } else {
                $this->error(self::CANNOT_DECODE_PUNYCODE);
                return false;
            }
        }

        return implode($decoded);
    }
}
