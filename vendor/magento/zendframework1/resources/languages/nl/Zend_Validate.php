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
 * EN-Revision: 22076
 */
return array(
    // Zend_Validate_Alnum
    "Invalid type given, value should be float, string, or integer" => "Ongeldig type opgegeven, waarde moet een float, string of integer zijn",
    "'%value%' contains characters which are non alphabetic and no digits" => "'%value%' bevat tekens welke alfabetisch, noch numeriek zijn",
    "'%value%' is an empty string" => "'%value%' is een lege string",

    // Zend_Validate_Alpha
    "Invalid type given, value should be a string" => "Ongeldig type opgegeven, waarde moet een string zijn",
    "'%value%' contains non alphabetic characters" => "'%value%' bevat tekens welke niet alfabetisch zijn",
    "'%value%' is an empty string" => "'%value%' is een lege string",

    // Zend_Validate_Barcode
    "'%value%' failed checksum validation" => "'%value%' slaagde niet in de checksum validatie",
    "'%value%' contains invalid characters" => "'%value%' bevat ongeldige tekens",
    "'%value%' should have a length of %length% characters" => "'%value%' moet een lengte hebben van %length% tekens",
    "Invalid type given, value should be string" => "Ongeldig type opgegeven, waarde moet een string zijn",

    // Zend_Validate_Between
    "'%value%' is not between '%min%' and '%max%', inclusively" => "'%value%' is niet tussen of gelijk aan '%min%' en '%max%'",
    "'%value%' is not strictly between '%min%' and '%max%'" => "'%value%' is niet tussen '%min%' en '%max%'",

    // Zend_Validate_Callback
    "'%value%' is not valid" => "'%value%' is ongeldig",
    "Failure within the callback, exception returned" => "Fout opgetreden in de callback, exceptie teruggegeven",

    // Zend_Validate_Ccnum
    "'%value%' must contain between 13 and 19 digits" => "'%value%' moet 13 tot 19 cijfers bevatten",
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "Het Luhn algoritme (mod-10 checksum) is niet gelukt op '%value%'",

    // Zend_Validate_CreditCard
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "Het Luhn algoritme (mod-10 checksum) is niet geslaagd op '%value%'",
    "'%value%' must contain only digits" => "'%value%' kan alleen cijfers bevatten",
    "Invalid type given, value should be a string" => "Ongeldig type opgegeven, waarde moet een string zijn",
    "'%value%' contains an invalid amount of digits" => "'%value%' bevat een ongeldige hoeveelheid cijfers",
    "'%value%' is not from an allowed institute" => "'%value%' is niet afkomstig van een toegestaan instituut",
    "Validation of '%value%' has been failed by the service" => "Validatie door de service van '%value%' is mislukt",
    "The service returned a failure while validating '%value%'" => "De service heeft een foutmelding teruggegeven bij het valideren van '%value%'",

    // Zend_Validate_Date
    "Invalid type given, value should be string, integer, array or Zend_Date" => "Ongeldig type opgegeven, waarde moet een string, integer, array of Zend_Date zijn",
    "'%value%' does not appear to be a valid date" => "'%value%' lijkt geen geldige datum te zijn",
    "'%value%' does not fit the date format '%format%'" => "'%value%' past niet in het datumformaat '%format%'",

    // Zend_Validate_Db_Abstract
    "No record matching '%value%' was found" => "Er kon geen record gevonden wat overeenkomt met %value%",
    "A record matching '%value%' was found" => "Een record wat overeenkomt met %value% is gevonden",

    // Zend_Validate_Digits
    "Invalid type given, value should be string, integer or float" => "Ongeldig type opgegeven, waarde moet een string, integer of float zijn",
    "'%value%' contains characters which are not digits; but only digits are allowed" => "'%value%' bevat niet enkel numerieke karakters",
    "'%value%' is an empty string" => "'%value%' is een lege string",

    // Zend_Validate_EmailAddress
    "Invalid type given, value should be a string" => "Ongeldig type opgegeven, waarde moet een string zijn",
    "'%value%' is not a valid email address in the basic format local-part@hostname" => "'%value%' is geen geldig e-mail adres in het basis formaat lokaal-gedeelte@hostname",
    "'%hostname%' is not a valid hostname for email address '%value%'" => "'%hostname%' is geen geldige hostnaam voor e-mail adres '%value%'",
    "'%hostname%' does not appear to have a valid MX record for the email address '%value%'" => "'%hostname%' lijkt geen geldig MX record te hebben voor e-mail adres '%value%'",
    "'%hostname%' is not in a routable network segment. The email address '%value%' should not be resolved from public network." => "'%hostname%' bevindt zich niet in een routeerbaar netwerk segment. Het e-mail adres '%value%' zou niet naar mogen worden verwezen vanaf een publiek netwerk.",
    "'%localPart%' can not be matched against dot-atom format" => "'%localPart%' kan niet worden gematched met het dot-atom formaat",
    "'%localPart%' can not be matched against quoted-string format" => "'%localPart%' kan niet worden gematched met het quoted-string formaat",
    "'%localPart%' is not a valid local part for email address '%value%'" => "'%localPart%' is geen geldig lokaal gedeelte voor e-mail adres '%value%'",
    "'%value%' exceeds the allowed length" => "'%value%' overschrijdt de toegestane lengte",

    // Zend_Validate_File_Count
    "Too many files, maximum '%max%' are allowed but '%count%' are given" => "Te veel bestanden, maximaal '%max%' zijn toegestaan, maar '%count%' werd opgegeven",
    "Too few files, minimum '%min%' are expected but '%count%' are given" => "Te weinig bestanden, er worden er minimaal '%min%' verwacht, maar er waren er  '%count%' opgegeven",

    // Zend_Validate_File_Crc32
    "File '%value%' does not match the given crc32 hashes" => "File '%value%' matcht niet met de opgegeven crc32 hashes",
    "A crc32 hash could not be evaluated for the given file" => "Fout tijdens het genereren van een crc32 hash van het opgegeven bestand",
    "File '%value%' could not be found" => "Het bestand '%value%' kon niet worden gevonden",

    // Zend_Validate_File_ExcludeExtension
    "File '%value%' has a false extension" => "Het bestand '%value%' heeft een ongeldige extensie",
    "File '%value%' could not be found" => "Het bestand '%value%' kon niet worden gevonden",

    // Zend_Validate_File_ExcludeMimeType
    "File '%value%' has a false mimetype of '%type%'" => "Het bestand '%value%' heeft een ongeldig mimetype: '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Het mimetype van bestand '%value%' kon niet worden gedetecteerd",
    "File '%value%' can not be read" => "Het bestand '%value%' kon niet worden gelezen",

    // Zend_Validate_File_Exists
    "File '%value%' does not exist" => "Bestand '%value%' bestaat niet",

    // Zend_Validate_File_Extension
    "File '%value%' has a false extension" => "Het bestand '%value%' heeft een ongeldige extensie",
    "File '%value%' could not be found" => "Het bestand '%value%' kon niet worden gevonden",

    // Zend_Validate_File_FilesSize
    "All files in sum should have a maximum size of '%max%' but '%size%' were detected" => "Alle bestanden tesamen hebben een maximale grootte van '%max%' maar '%size%' was gedetecteerd",
    "All files in sum should have a minimum size of '%min%' but '%size%' were detected" => "Alle bestanden tesamen hebben een minimum grotte van '%min%' maar '%size%' was gedetecteerd",
    "One or more files can not be read" => "Eén of meer bestanden konden niet worden gelezen",

    // Zend_Validate_File_Hash
    "File '%value%' does not match the given hashes" => "Het bestand '%value%' matcht niet met de opgegeven hashes",
    "A hash could not be evaluated for the given file" => "Een hash kon niet worden gegenereerd voor het opgegeven bestand",
    "File '%value%' could not be found" => "Het bestand '%value%' kon niet worden gevonden",

    // Zend_Validate_File_ImageSize
    "Maximum allowed width for image '%value%' should be '%maxwidth%' but '%width%' detected" => "Maximum breedte voor afbeelding '%value%' is '%maxwidth%' maar '%width%' werd gedetecteerd",
    "Minimum expected width for image '%value%' should be '%minwidth%' but '%width%' detected" => "Minimum breedte voor afbeelding '%value%' is '%minwidth%' maar '%width%' werd gedetecteerd",
    "Maximum allowed height for image '%value%' should be '%maxheight%' but '%height%' detected" => "Maximum hoogte voor afbeelding '%value%' is '%maxheight%' maar '%height%' werd gedetecteerd",
    "Minimum expected height for image '%value%' should be '%minheight%' but '%height%' detected" => "Minimum hoogte voor afbeelding '%value%' is '%minheight%' maar '%height%' werd gedetecteerd",
    "The size of image '%value%' could not be detected" => "De grootte van afbeelding '%value%' kon niet worden gedetecteerd",
    "File '%value%' can not be read" => "Het bestand '%value%' kan niet worden gelezen",

    // Zend_Validate_File_IsCompressed
    "File '%value%' is not compressed, '%type%' detected" => "Het bestand '%value%' is niet gecomprimeerd, '%type%' gedetecteerd",
    "The mimetype of file '%value%' could not be detected" => "Het mimetype van bestand '%value%' kon niet worden gedetecteerd",
    "File '%value%' can not be read" => "Bestand '%value%' kan niet worden gelezen",

    // Zend_Validate_File_IsImage
    "File '%value%' is no image, '%type%' detected" => "Het bestand '%value%' is geen afbeelding, '%type%' gedetecteerd",
    "The mimetype of file '%value%' could not be detected" => "Het mimetype van bestand '%value%' kon niet worden gedetecteerd",
    "File '%value%' can not be read" => "Het bestand '%value%' kon niet worden gelezen",

    // Zend_Validate_File_Md5
    "File '%value%' does not match the given md5 hashes" => "Het bestand '%value%' matcht niet met de opgegeven md5-hashes",
    "A md5 hash could not be evaluated for the given file" => "Een md5-hash kon niet gegenereerd worden voor het opgegeven bestand",
    "File '%value%' could not be found" => "Het bestand '%value%' kon niet worden gevonden",

    // Zend_Validate_File_MimeType
    "File '%value%' has a false mimetype of '%type%'" => "Het bestand '%value%' heeft een ongeldig mimetype: '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Het mimetype van bestand '%value%' kon niet worden gedetecteerd",
    "File '%value%' can not be read" => "Het bestand '%value%' kon niet worden gelezen",

    // Zend_Validate_File_NotExists
    "File '%value%' exists" => "Het bestand '%value%' bestaat",

    // Zend_Validate_File_Sha1
    "File '%value%' does not match the given sha1 hashes" => "Het bestand '%value%' matcht niet met de opgegeven sha1-hashes",
    "A sha1 hash could not be evaluated for the given file" => "Een sha1-hash kon niet worden gegenereerd voor het opgegeven bestand",
    "File '%value%' could not be found" => "Het bestand '%value%' kon niet worden gevonden",

    // Zend_Validate_File_Size
    "Maximum allowed size for file '%value%' is '%max%' but '%size%' detected" => "Maximum grootte voor bestand '%value%' is '%max%' maar '%size%' werd gedetecteerd",
    "Minimum expected size for file '%value%' is '%min%' but '%size%' detected" => "Minimum grootte voor bestand '%value%' is '%min%' maar '%size%' werd gedetecteerd",
    "File '%value%' could not be found" => "Het bestand '%value%' kon niet worden gevonden",

    // Zend_Validate_File_Upload
    "File '%value%' exceeds the defined ini size" => "Het bestand '%value%' overschrijdt de ini grootte",
    "File '%value%' exceeds the defined form size" => "Het bestand '%value%' overschrijdt de formulier grootte",
    "File '%value%' was only partially uploaded" => "Het bestand '%value%' was slechts gedeeltelijk geüpload",
    "File '%value%' was not uploaded" => "Het bestand '%value%' was niet geüpload",
    "No temporary directory was found for file '%value%'" => "Geen tijdelijke map was gevonden voor bestand '%value%'",
    "File '%value%' can't be written" => "Het bestand '%value%' kan niet worden geschreven",
    "A PHP extension returned an error while uploading the file '%value%'" => "Een PHP-extensie gaf een foutmelding terug tijdens het uploaden van het bestand '%value%'",
    "File '%value%' was illegally uploaded. This could be a possible attack" => "Het bestand '%value%' was illegaal geüpload. Dit kan een aanval zijn",
    "File '%value%' was not found" => "Het bestand '%value%' kon niet worden gevonden",
    "Unknown error while uploading file '%value%'" => "Er is een onbekende fout opgetreden tijdens het uploaden van '%value%'",

    // Zend_Validate_File_WordCount
    "Too much words, maximum '%max%' are allowed but '%count%' were counted" => "Te veel woorden, er is een maximum van '%max%', maar er waren '%count%' geteld",
    "Too less words, minimum '%min%' are expected but '%count%' were counted" => "Te weinig worden, er is een minimum van '%min%' maar er waren '%count%' geteld",
    "File '%value%' could not be found" => "Het bestand '%value%' kon niet worden gevonden",

    // Zend_Validate_Float
    "Invalid type given, value should be float, string, or integer" => "Ongeldig type opgegeven, waarde moet een float, string, of integer zijn",
    "'%value%' does not appear to be a float" => "'%value%' lijkt geen float te zijn",

    // Zend_Validate_GreaterThan
    "'%value%' is not greater than '%min%'" => "'%value%' is niet groter dan '%min%'",

    // Zend_Validate_Hex
    "Invalid type given, value should be a string" => "Ongeldig type gegeven, waarde moet een string zijn",
    "'%value%' has not only hexadecimal digit characters" => "'%value%' bestaat niet enkel uit acht hexadecimale cijfers",

    // Zend_Validate_Hostname
    "Invalid type given, value should be a string" => "Ongeldig type gegeven, waarde moet een string zijn",
    "'%value%' appears to be an IP address, but IP addresses are not allowed" => "'%value%' lijkt een IP adres te zijn, maar IP adressen zijn niet toegestaan",
    "'%value%' appears to be a DNS hostname but cannot match TLD against known list" => "'%value%' lijkt een DNS hostnaam te zijn, maar het TLD bestaat niet in de lijst met bekende TLD's",
    "'%value%' appears to be a DNS hostname but contains a dash in an invalid position" => "'%value%' lijkt een DNS hostnaam te zijn, maar bevat een streep op een ongeldige plek",
    "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'" => "'%value%' lijkt een DNS hostnaam te zijn, maar past niet in het hostnaam-schema voor TLD '%tld%'",
    "'%value%' appears to be a DNS hostname but cannot extract TLD part" => "'%value%' lijkt een DNS hostnaam te zijn, maar kan niet het TLD gedeelte bepalen",
    "'%value%' does not match the expected structure for a DNS hostname" => "'%value%' matcht niet met de verwachte structuur voor een DNS hostnaam",
    "'%value%' does not appear to be a valid local network name" => "'%value%' lijkt geen geldige lokale netwerknaam te zijn",
    "'%value%' appears to be a local network name but local network names are not allowed" => "'%value%' lijkt een lokale netwerknaam te zijn, welke niet zijn toegestaan",
    "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded" => "'%value%' lijkt een geldige DNS hostnaam te zijn, maar de opgegeven punnycode notatie kan niet worden gedecodeerd",

    // Zend_Validate_Iban
    "Unknown country within the IBAN '%value%'" => "Onbekend land in de IBAN '%value%'",
    "'%value%' has a false IBAN format" => "'%value%' heeft een ongeldig IBAN formaat",
    "'%value%' has failed the IBAN check" => "'%value%' is geen geldige IBAN",

    // Zend_Validate_Identical
    "The two given tokens do not match" => "De twee tokens komen niet overeen",
    "No token was provided to match against" => "Er is geen token opgegeven om mee te matchen",

    // Zend_Validate_InArray
    "'%value%' was not found in the haystack" => "'%value%' kon niet worden gevonden in lijst met beschikbare waardes",

    // Zend_Validate_Int
    "Invalid type given, value should be string or integer" => "Ongeldig type opgegeven, waarde moet een string of integer zijn",
    "'%value%' does not appear to be an integer" => "'%value%' lijkt geen integer te zijn",

    // Zend_Validate_Ip
    "Invalid type given, value should be a string" => "Ongeldig type gegeven, waarde moet een string zijn",
    "'%value%' does not appear to be a valid IP address" => "'%value%' lijkt geen geldig IP adres te zijn",

    // Zend_Validate_Isbn
    "Invalid type given, value should be string or integer" => "Ongeldig type opgegeven, waarde moet een string of integer zijn",
    "'%value%' is not a valid ISBN number" => "'%value%' is geen geldig ISBN nummer",

    // Zend_Validate_LessThan
    "'%value%' is not less than '%max%'" => "'%value%' is niet minder dan '%max%'",

    // Zend_Validate_NotEmpty
    "Invalid type given, value should be float, string, array, boolean or integer" => "Ongeldig type opgegeven, waarde dient een float, string, array, boolean of integer te zijn",
    "Value is required and can't be empty" => "Waarde is vereist en kan niet leeg worden gelaten",

    // Zend_Validate_PostCode
    "Invalid type given. The value should be a string or a integer" => "Ongeldig type opgegeven, waarde moet een string of integer zijn",
    "'%value%' does not appear to be a postal code" => "'%value%' lijkt geen geldige postcode te zijn",

    // Zend_Validate_Regex
    "Invalid type given, value should be string, integer or float" => "Ongeldig type opgegeven, waarde dient een string, integer of float te zijn",
    "'%value%' does not match against pattern '%pattern%'" => "'%value%' matcht niet met het patroon '%pattern%'",
    "There was an internal error while using the pattern '%pattern%'" => "Er is een interne fout opgetreden tijdens het gebruik van het patroon '%pattern%'",

    // Zend_Validate_Sitemap_Changefreq
    "'%value%' is not a valid sitemap changefreq" => "'%value%' is geen geldige sitemap changefreq",
    "Invalid type given, the value should be a string" => "Ongeldig type opgegeven, waarde dient een string te zijn",

    // Zend_Validate_Sitemap_Lastmod
    "'%value%' is not a valid sitemap lastmod" => "'%value%' is geen geldige sitemap lastmod",
    "Invalid type given, the value should be a string" => "Ongeldig type opgegeven, waarde dient een string te zijn",

    // Zend_Validate_Sitemap_Loc
    "'%value%' is not a valid sitemap location" => "'%value%' is geen geldige sitemap locatie",
    "Invalid type given, the value should be a string" => "Ongeldig type opgegeven, waarde dient een string te zijn",

    // Zend_Validate_Sitemap_Priority
    "'%value%' is not a valid sitemap priority" => "'%value%' is geen geldige sitemap prioriteit",
    "Invalid type given, the value should be a integer, a float or a numeric string" => "Ongeldig type opgegeven, waarde dient een integer, float of een numerieke string te zijn",

    // Zend_Validate_StringLength
    "Invalid type given, value should be a string" => "Ongeldig type opgegeven, waarde dient een string te zijn",
    "'%value%' is less than %min% characters long" => "'%value%' is minder dan %min% tekens lang",
    "'%value%' is more than %max% characters long" => "'%value%' is meer dan %max% tekens lang",
);
