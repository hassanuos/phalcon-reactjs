<?php

class RequestApi{

    public $apiBaseURL = "https://api.exchangerate.host";
    const GET  = 'GET';
    const POST = 'POST';
    const PUT  = 'PUT';
    /**
     * @throws CurlException
     */
    public static function get($url, $params = [], $options = []){
        list ($status, $headers, $body) = static::doCurl(static::GET, $url, $params, $options);
        return $body;
    }

    /**
     * @throws CurlException
     */
    public static function post($url, $params = [], $options = []){
        list ($status, $headers, $body) = static::doCurl(static::POST, $url, $params, $options);
        return $body;
    }

    /**
     * @throws CurlException
     */
    public static function put($url, $params = [], $options = []){
        list ($status, $headers, $body) = static::doCurl(static::PUT, $url, $params, $options);
        return $body;
    }

    /**
     * @throws CurlException
     */
    public static function doCurl($method, $url, $params = [], $options = []){
        $defaults = [
            'headers'          => [],
            'verify'           => true,
            'timeout'          => 5,
            'follow_redirects' => true,
            'user_agent'       => null,
            'proxy'            => null,
            'password'         => null,
            'cookie'           => null,
            'json_post'        => false,
        ];

        $invalid = array_diff_key($options, $defaults);
        if ($invalid){
            throw new DomainException('Invalid curl options: ' . join(', ', array_keys($invalid)));
        }

        $options = array_merge($defaults, $options);

        if (!in_array($method, [static::GET, static::POST, static::PUT])){
            throw new DomainException('Invalid method: ' . $method);
        }

        if ($options['headers'] && $options['headers'] !== array_values($options['headers'])){
            throw new DomainException('Headers must not be an associative array. Its a simple array with values of the form "Header: value"');
        }

        $ch = curl_init();

        if ($method == static::GET && $params){
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => $options['headers'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => $options['follow_redirects'],
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => $options['timeout'],
            CURLOPT_SSL_VERIFYPEER => $options['verify'],
            CURLOPT_USERAGENT      => $options['user_agent'],
        ]);

        $postFields = '';
        if ($method === static::POST || $method === static::PUT){
            $postFields = $options['json_post'] ? json_encode($params) : http_build_query($params);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            if ($method === static::POST){
                curl_setopt($ch, CURLOPT_POST, true);
            }else{
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            }
        }

        if ($options['proxy']){
            curl_setopt($ch, CURLOPT_PROXY, $options['proxy']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        }

        if ($options['password']){
            curl_setopt($ch, CURLOPT_USERPWD, $options['password']);
        }

        if ($options['cookie']){
            curl_setopt($ch, CURLOPT_COOKIE, $options['cookie']);
        }

        $startingResponse = false;
        $responseHeaders  = [];
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $h) use (&$responseHeaders, &$startingResponse){
            $value = trim($h);
            if ($value === ''){
                $startingResponse = true;
            }elseif ($startingResponse){
                $startingResponse = false;
                $responseHeaders  = [$value];
            }else{
                $responseHeaders[] = $value;
            }
            return strlen($h);
        });

        $responseContent = curl_exec($ch);
        $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)){
            throw new CurlException($ch);
        }
        curl_close($ch);

        // DEBUGGING
        $curlString = 'curl ' . escapeshellarg($url) . ' --compressed ';
        foreach ($options['headers'] as $header){
            $curlString .= '-H ' . escapeshellarg($header) . ' ';
        }
        if ($method === static::POST || $method === static::PUT){
            $curlString .= '--request ' . $method . ' --data ' . escapeshellarg($postFields) . ' ';
        }

        return [$statusCode, $responseHeaders, $responseContent, trim($curlString)];
    }

    public static function cacheExpiryTime($cacheKey){
        $cache_file = APP_PATH . "/cache/cache".$cacheKey;
        if (file_exists($cache_file) && (filemtime($cache_file) > (time() - (60 * 5)))) {
            $diff = filemtime($cache_file) - (time() - 60 * 5);
            if (1 > $diff) {
                return 0;
            } else {
                $m = $diff / 60 % 60;
                $s = $diff % 60;
                $totalSecondsRemaining = ($m * 60) + $s;
            }

            return $totalSecondsRemaining;
        }

        return 0;
    }

    public static function codeToCountry($code){

        $code = strtoupper($code);
        $countryList = [
            'AF' => 'Afghanistan',
            'AX' => 'Aland Islands',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas the',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island (Bouvetoya)',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory (Chagos Archipelago)',
            'VG' => 'British Virgin Islands',
            'BN' => 'Brunei Darussalam',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos (Keeling) Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros the',
            'CD' => 'Congo',
            'CG' => 'Congo the',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'CI' => 'Cote d\'Ivoire',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FO' => 'Faroe Islands',
            'FK' => 'Falkland Islands (Malvinas)',
            'FJ' => 'Fiji the Fiji Islands',
            'FI' => 'Finland',
            'FR' => 'France, French Republic',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia the',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island and McDonald Islands',
            'VA' => 'Holy See (Vatican City State)',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KP' => 'Korea',
            'KR' => 'Korea',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyz Republic',
            'LA' => 'Lao',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libyan Arab Jamahiriya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macao',
            'MK' => 'Macedonia',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico',
            'FM' => 'Micronesia',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'AN' => 'Netherlands Antilles',
            'NL' => 'Netherlands the',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territory',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn Islands',
            'PL' => 'Poland',
            'PT' => 'Portugal, Portuguese Republic',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RE' => 'Reunion',
            'RO' => 'Romania',
            'RU' => 'Russian Federation',
            'RW' => 'Rwanda',
            'BL' => 'Saint Barthelemy',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin',
            'PM' => 'Saint Pierre and Miquelon',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome and Principe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia (Slovak Republic)',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia, Somali Republic',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard & Jan Mayen Islands',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland, Swiss Confederation',
            'SY' => 'Syrian Arab Republic',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States of America',
            'UM' => 'United States Minor Outlying Islands',
            'VI' => 'United States Virgin Islands',
            'UY' => 'Uruguay, Eastern Republic of',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'WF' => 'Wallis and Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
            'XS' => 'XS',
            'EU' => 'Europe Union',
            'XP' => 'XP',
            'SS' => 'SS',
            'XO' => 'XO',
            'XA' => 'XA',
            'XD' => 'XD',
            'XC' => 'XC',
        ];

        return ( !isset($countryList[$code]) && !$countryList[$code] ) ? $code : $countryList[$code];
    }
}