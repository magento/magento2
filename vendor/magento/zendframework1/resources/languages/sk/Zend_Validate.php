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
 * @package    Zend_Translate
 * @subpackage Ressource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id:$
 */

/**
 * SK-Revision: 22668
 */
return array(
    // Zend_Validate_Alnum
    "Invalid type given. String, integer or float expected" => "Chybný typ. Bol očakávaný reťazec, celé alebo desatinné číslo",
    "'%value%' contains characters which are non alphabetic and no digits" => "'%value%' obsahuje aj iné znaky ako písmena a číslice",
    "'%value%' is an empty string" => "'%value%' je prázdny reťazec",

    // Zend_Validate_Alpha
    "Invalid type given. String expected" => "Chybný typ. Bol očakávaný reťazec",
    "'%value%' contains non alphabetic characters" => "'%value%' obsahuje aj iné znaky ako písmena",
    "'%value%' is an empty string" => "'%value%' je prázdny reťazec",

    // Zend_Validate_Barcode
    "'%value%' failed checksum validation" => "'%value%' má chybný kontrolný súčet",
    "'%value%' contains invalid characters" => "'%value%' obsahuje neplatné znaky",
    "'%value%' should have a length of %length% characters" => "'%value%' by mal mať dĺžku %length% znakov",
    "Invalid type given. String expected" => "Chybný typ. Bol očakávaný reťazec.",

    // Zend_Validate_Between
    "'%value%' is not between '%min%' and '%max%', inclusively" => "'%value%' nie je medzi '%min%' a '%max%', vrátane",
    "'%value%' is not strictly between '%min%' and '%max%'" => "'%value%' nie je medzi '%min%' a '%max%'",

    // Zend_Validate_Callback
    "'%value%' is not valid" => "Hodnota '%value%' je neplatná",
    "An exception has been raised within the callback" => "Počas validácie došlo chybe",

    // Zend_Validate_Ccnum
    "'%value%' must contain between 13 and 19 digits" => "'%value%' musí obsahovať 13 až 19 číslic",
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "Luhnov algoritmus (kontrolný súčet mod-10) nevyšiel pre '%value%'",

    // Zend_Validate_CreditCard
    "'%value%' seems to contain an invalid checksum" => "'%value%' obsahuje neplatný kontrolný súčet",
    "'%value%' must contain only digits" => "'%value%' musí obsahovať len čísla",
    "Invalid type given. String expected" => "Chybný typ. Bol očakávaný reťazec",
    "'%value%' contains an invalid amount of digits" => "'%value%' obsahuje neplatný počet číslic",
    "'%value%' is not from an allowed institute" => "'%value%' je od nepovolenej spoločnosti",
    "'%value%' seems to be an invalid creditcard number" => "'%value%' je neplatné číslo kreditnej karty",
    "An exception has been raised while validating '%value%'" => "Počas validácie '%value%' došlo k chybe",

    // Zend_Validate_Date
    "Invalid type given. String, integer, array or Zend_Date expected" => "Chybný typ. Bol očakávaný reťazec, číslo, pole alebo objekt typu Zend_Date",
    "'%value%' does not appear to be a valid date" => "'%value%' je neplatný dátum",
    "'%value%' does not fit the date format '%format%'" => "'%value%' nie je formátu '%format%'",

    // Zend_Validate_Db_Abstract
    "No record matching '%value%' was found" => "Nebol nájdený žiadny záznam zhodný s '%value%'",
    "A record matching '%value%' was found" => "Bol nájdený záznam zhodný s '%value%'",

    // Zend_Validate_Digits
    "Invalid type given. String, integer or float expected" => "Chybný typ. Bol očakávaný reťazec, celé alebo desatinné číslo",
    "'%value%' must contain only digits" => "'%value%' musí obsahovať iba číslice",
    "'%value%' is an empty string" => "'%value%' je prázdny reťazec",

    // Zend_Validate_EmailAddress
    "Invalid type given. String expected" => "Chybný typ. Bol očakávaný reťazec",
    "'%value%' is not a valid email address in the basic format local-part@hostname" => "'%value%' nie je platná e-mailová adresa v tvare local-part@hostname",
    "'%hostname%' is not a valid hostname for email address '%value%'" => "'%hostname%' je neplatný hostname pre emailovou adresu '%value%'",
    "'%hostname%' does not appear to have a valid MX record for the email address '%value%'" => "'%hostname%' neobsahuje platný MX záznam pre e-mailovou adresu '%value%'",
    "'%hostname%' is not in a routable network segment. The email address '%value%' should not be resolved from public network" => "'%hostname%' nie je v smerovateľnom úseku siete. E-mailová adresa '%value%' by nemala byť požadovaná z verejnej siete",
    "'%localPart%' can not be matched against dot-atom format" => "'%localPart%' nemôže byť porovnaný proti dot-atom formátu",
    "'%localPart%' can not be matched against quoted-string format" => "'%localPart%' nemôže byť porovnaný proti quoted-string formátu",
    "'%localPart%' is not a valid local part for email address '%value%'" => "'%localPart%' je neplatná 'local part' pre e-mailovou adresu '%value%'",
    "'%value%' exceeds the allowed length" => "'%value%' prekročil dovolenú dĺžku",

    // Zend_Validate_File_Count
    "Too many files, maximum '%max%' are allowed but '%count%' are given" => "Príliš veľa súborov. Maximum je '%max%', ale bolo zadaných '%count%'",
    "Too few files, minimum '%min%' are expected but '%count%' are given" => "Píliš málo súborov. Minimum je '%min%', ale bolo zadaných len '%count%'",

    // Zend_Validate_File_Crc32
    "File '%value%' does not match the given crc32 hashes" => "Súbor '%value%' nezodpovedá zadanému crc32 hashu",
    "A crc32 hash could not be evaluated for the given file" => "Pre zadaný súbor nemohol byť vypočítaný crc32 hash",
    "File '%value%' is not readable or does not exist" => "Súbor '%value%' je nečitateľný alebo neexistuje",

    // Zend_Validate_File_ExcludeExtension
    "File '%value%' has a false extension" => "Súbor '%value%' má nesprávnu príponu",
    "File '%value%' is not readable or does not exist" => "Súbor '%value%' je nečitateľný alebo neexistuje",

    // Zend_Validate_File_ExcludeMimeType
    "File '%value%' has a false mimetype of '%type%'" => "Súbor '%value%' má nesprávny mimetyp '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Mimetyp súboru '%value%' nebolo možne zistiť",
    "File '%value%' is not readable or does not exist" => "Súbor '%value%' je nečitateľný alebo neexistuje",

    // Zend_Validate_File_Exists
    "File '%value%' does not exist" => "Súbor '%value%' neexistuje",

    // Zend_Validate_File_Extension
    "File '%value%' has a false extension" => "Súbor '%value%' má nesprávnu príponu",
    "File '%value%' is not readable or does not exist" => "Súbor '%value%' je nečitateľný alebo neexistuje",

    // Zend_Validate_File_FilesSize
    "All files in sum should have a maximum size of '%max%' but '%size%' were detected" => "Súčet veľkostí všetkých súborov by mal byť maximálne '%max%', ale je '%size%'",
    "All files in sum should have a minimum size of '%min%' but '%size%' were detected" => "Súčet veľkosti všetkých súborov by mal byť minimálne '%min%', ale je '%size%'",
    "One or more files can not be read" => "Jeden alebo viac súborov nie je možné načítať",

    // Zend_Validate_File_Hash
    "File '%value%' does not match the given hashes" => "Súbor '%value%' neodpovedá danému hashu",
    "A hash could not be evaluated for the given file" => "Hash nemohol byť pre daný súbor vypočítaný",
    "File '%value%' is not readable or does not exist" => "Súbor '%value%' je nečitateľný alebo neexistuje",

    // Zend_Validate_File_ImageSize
    "Maximum allowed width for image '%value%' should be '%maxwidth%' but '%width%' detected" => "Maximálna šírka obrázka '%value%' by mala byť '%maxwidth%', ale je '%width%'",
    "Minimum expected width for image '%value%' should be '%minwidth%' but '%width%' detected" => "Minimálna šírka obrázka '%value%' by mala byť '%minwidth%', ale je '%width%'",
    "Maximum allowed height for image '%value%' should be '%maxheight%' but '%height%' detected" => "Maximálna výška obrázka '%value%' by mala byť '%maxheight%', ale je '%height%'",
    "Minimum expected height for image '%value%' should be '%minheight%' but '%height%' detected" => "Minimálna výška obrázka '%value%' by mala byť '%minheight%', ale je '%height%'",
    "The size of image '%value%' could not be detected" => "Rozmery obrázka '%value%' nebolo možné zistiť",
    "File '%value%' is not readable or does not exist" => "Súbor '%value%' je nečitateľný alebo neexistuje",

    // Zend_Validate_File_IsCompressed
    "File '%value%' is not compressed, '%type%' detected" => "Súbor '%value%' nie je komprimovaný, typ súboru '%type%' bol zistený",
    "The mimetype of file '%value%' could not be detected" => "Mimetyp súboru '%value%' nebolo možné zistiť",
    "File '%value%' is not readable or does not exist" => "Súbor '%value%' je nečitateľný alebo neexistuje",

    // Zend_Validate_File_IsImage
    "File '%value%' is no image, '%type%' detected" => "Súbor '%value%' nie je obrázok, typ súboru '%type%' bol zistený",
    "The mimetype of file '%value%' could not be detected" => "Mimetyp súboru '%value%' nebolo možné zistiť",
    "File '%value%' is not readable or does not exist" => "Súbor '%value%' je nečitateľný alebo neexistuje",

    // Zend_Validate_File_Md5
    "File '%value%' does not match the given md5 hashes" => "Súbor '%value%' neodpovedá danému md5 hashu",
    "A md5 hash could not be evaluated for the given file" => "md5 hash nemohol byť pre daný súbor vypočítaný",
    "File '%value%' is not readable or does not exist" => "Súbor '%value%' je nečitateľný alebo neexistuje",

    // Zend_Validate_File_MimeType
    "File '%value%' has a false mimetype of '%type%'" => "Súbor '%value%' má neplatný mimetyp '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Mimetyp súboru '%value%' nebolo možne zistiť",
    "File '%value%' is not readable or does not exist" => "Soubor '%value%' je nečitateľný alebo neexistuje",

    // Zend_Validate_File_NotExists
    "File '%value%' exists" => "Súbor '%value%' už existuje",

    // Zend_Validate_File_Sha1
    "File '%value%' does not match the given sha1 hashes" => "Súbor '%value%' neodpovedá danému sha1 hashu",
    "A sha1 hash could not be evaluated for the given file" => "sha1 hash nemohol byť pre daný súbor vypočítaný",
    "File '%value%' is not readable or does not exist" => "Súbor '%value%' je nečitateľný alebo neexistuje",

    // Zend_Validate_File_Size
    "Maximum allowed size for file '%value%' is '%max%' but '%size%' detected" => "Maximálna povolená veľkosť súboru je '%max%', ale '%value%' má '%size%'",
    "Minimum expected size for file '%value%' is '%min%' but '%size%' detected" => "Minimálna veľkosť súboru je '%min%', ale '%value%' má '%size%'",
    "File '%value%' is not readable or does not exist" => "Súbor '%value%' je nečitateľný alebo neexistuje",

    // Zend_Validate_File_Upload
    "File '%value%' exceeds the defined ini size" => "Súbor '%value%' prekročil veľkosť definovanú v ini súbore",
    "File '%value%' exceeds the defined form size" => "Súbor '%value%' prekročil veľkosť definovanú vo formulári",
    "File '%value%' was only partially uploaded" => "Súbor '%value%' bol nahraný len čiastočne",
    "File '%value%' was not uploaded" => "Súbor '%value%' nebol nahraný",
    "No temporary directory was found for file '%value%'" => "Pre súbor '%value%' nebol nájdený žiadny dočasný adresár",
    "File '%value%' can't be written" => "Súbor '%value%' nemôže byť zapísaný",
    "A PHP extension returned an error while uploading the file '%value%'" => "PHP rozšírenie vrátilo chybu počas nahrávania súboru '%value%'",
    "File '%value%' was illegally uploaded. This could be a possible attack" => "Súbor '%value%' bol nahraný neoprávnene. Môže sa jednať o útok",
    "File '%value%' was not found" => "Súbor '%value%' nebol nalezený",
    "Unknown error while uploading file '%value%'" => "Počas nahrávania súboru '%value%' došlo k chybe",

    // Zend_Validate_File_WordCount
    "Too much words, maximum '%max%' are allowed but '%count%' were counted" => "Príliš mnoho slov. Je ich dovolené maximálne '%max%', ale bolo zadaných '%count%'",
    "Too less words, minimum '%min%' are expected but '%count%' were counted" => "Príliš málo slov. Musí ich byť minimálne '%min%', ale bolo zadaných len '%count%'",
    "File '%value%' is not readable or does not exist" => "Súbor '%value%' je nečitateľný alebo neexistuje",

    // Zend_Validate_Float
    "Invalid type given. String, integer or float expected" => "Chybný typ. Bol očakávaný reťazec, celé alebo desatinné číslo",
    "'%value%' does not appear to be a float" => "'%value%' nie je desatinné číslo",

    // Zend_Validate_GreaterThan
    "'%value%' is not greater than '%min%'" => "'%value%' nie je viac ako '%min%'",

    // Zend_Validate_Hex
    "Invalid type given. String expected" => "Chybný typ. Bol očakávaný reťazec",
    "'%value%' has not only hexadecimal digit characters" => "'%value%' musí obsahovať len znaky hexadecimálnych čísel.",

    // Zend_Validate_Hostname
    "Invalid type given. String expected" => "Chybný typ. Bol očakávaný reťazec",
    "'%value%' appears to be an IP address, but IP addresses are not allowed" => "'%value%' vyzerá ako IP adresa, ale tie nie sú dovolené",
    "'%value%' appears to be a DNS hostname but cannot match TLD against known list" => "'%value%' vyzerá ako hostname, ale nemohol byť overený proti známym TLD",
    "'%value%' appears to be a DNS hostname but contains a dash in an invalid position" => "'%value%' vyzerá ako hostname, ale obsahuje pomlčku na nepovolenom mieste",
    "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'" => "'%value%' vyzerá ako hostname, ale neodpovedá formátu hostname pre '%tld%'",
    "'%value%' appears to be a DNS hostname but cannot extract TLD part" => "'%value%' síce vyzerá ako hostname, ale nebolo možné určiť TLD",
    "'%value%' does not match the expected structure for a DNS hostname" => "'%value%' nezodpovedá očakávanej štruktúre hostname",
    "'%value%' does not appear to be a valid local network name" => "'%value%' nevyzerá ako platné sieťové meno",
    "'%value%' appears to be a local network name but local network names are not allowed" => "'%value%' vyzerá ako hostname lokálnej siete, tie ale nie sú dovolené",
    "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded" => "'%value%' vyzerá ako DNS hostname ale zadanú punycode notáciu nie je možné dekódovať",

    // Zend_Validate_Iban
    "Unknown country within the IBAN '%value%'" => "Neznámy štát v IBAN '%value%'",
    "'%value%' has a false IBAN format" => "'%value%' je neplatný formát IBAN",
    "'%value%' has failed the IBAN check" => "'%value%' neprešiel kontrolou IBAN",

    // Zend_Validate_Identical
    "The two given tokens do not match" => "Zadané položky sú rozdielne",
    "No token was provided to match against" => "Nebola zadaná položka na porovnanie",

    // Zend_Validate_InArray
    "'%value%' was not found in the haystack" => "'%value%' nebola nájdená v zozname",

    // Zend_Validate_Int
    "Invalid type given. String or integer expected" => "Chybný typ. Bol očakávaný reťazec alebo celé číslo",
    "'%value%' does not appear to be an integer" => "'%value%' nie je celé číslo",

    // Zend_Validate_Ip
    "Invalid type given. String expected" => "Chybný typ. Bol očakávaný reťazec",
    "'%value%' does not appear to be a valid IP address" => "'%value%' je neplatná IP adresa",

    // Zend_Validate_Isbn
    "Invalid type given. String or integer expected" => "Chybný typ. Bol očakávaný reťazec alebo celé číslo",
    "'%value%' is not a valid ISBN number" => "'%value%' je neplatný ISBN",

    // Zend_Validate_LessThan
    "'%value%' is not less than '%max%'" => "'%value%' nie je menej ako '%max%'",

    // Zend_Validate_NotEmpty
    "Invalid type given. String, integer, float, boolean or array expected" => "Chybný typ. Bol očakávaný reťazec, celé alebo desatinné číslo, boolean alebo pole",
    "Value is required and can't be empty" => "Položka je povinná a nemôže byť prázdna",

    // Zend_Validate_PostCode
    "Invalid type given. String or integer expected" => "Chybný typ. Bol očakávaný reťazec alebo celé číslo",
    "'%value%' does not appear to be a postal code" => "'%value%' nevyzerá ako PSČ",

    // Zend_Validate_Regex
    "Invalid type given. String, integer or float expected" => "Chybný typ. Bol očakávaný reťazec, celé alebo desatinné číslo",
    "'%value%' does not match against pattern '%pattern%'" => "'%value%' neodpovedá šablóne '%pattern%'",
    "There was an internal error while using the pattern '%pattern%'" => "Počas spracovania šablóny '%pattern%' došlo k interní chybe",

    // Zend_Validate_Sitemap_Changefreq
    "'%value%' is not a valid sitemap changefreq" => "'%value%' je neplatný 'changefreq' pre sitemapu",
    "Invalid type given. String expected" => "Chybný typ. Bol očakávaný reťazec",

    // Zend_Validate_Sitemap_Lastmod
    "'%value%' is not a valid sitemap lastmod" => "'%value%' je neplatný 'lastmod' pre sitemapu",
    "Invalid type given. String expected" => "Chybný typ. Bol očakávaný reťazec",

    // Zend_Validate_Sitemap_Loc
    "'%value%' is not a valid sitemap location" => "'%value%' je neplatný 'location' pre sitemapu",
    "Invalid type given. String expected" => "Chybný typ. Bol očakávaný reťazec",

    // Zend_Validate_Sitemap_Priority
    "'%value%' is not a valid sitemap priority" => "'%value%' je neplatná 'priority' pre sitemapu",
    "Invalid type given. Numeric string, integer or float expected" => "Chybný typ. Bol očakávaný číselný reťazec, celé alebo desatinné číslo.",

    // Zend_Validate_StringLength
    "Invalid type given. String expected" => "Chybný typ. Bol očakávaný reťazec",
    "'%value%' is less than %min% characters long" => "'%value%' ma menej ako %min% znakov",
    "'%value%' is more than %max% characters long" => "'%value%' ma viac ako %max% znakov",
);
