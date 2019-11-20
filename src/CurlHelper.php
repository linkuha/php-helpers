<?php
/**
 * Created by PhpStorm.
 * User: linkuha (Pudich Aleksandr)
 * Date: 13.11.2018
 * Time: 0:16
 */

namespace SimpleLibs;

/**
 * Class CurlHelper
 * @package classes\Helper
 * @version 1.0.0rc1
 */
class CurlHelper
{
    const PARAM_HTTP_TIMEOUT = 60;

    public static $uaDesktops = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
        'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
        'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.0.9) Gecko/20061206 Firefox/1.5.0.9',
        'Mozilla/5.0 (MSIE 10.0; Windows NT 6.1; Trident/5.0)',
        'Opera/9.80 (Windows NT 6.0) Presto/2.12.388 Version/12.14',
    ];

    public static $uaMobileAndroid = [
        'Dalvik/2.1.0 (Linux; U; Android 6.0.1; Redmi Note 4 MIUI/V8.2.10.0.MCFMIDL)',
        'Dalvik/2.1.0 (Linux; U; Android 7.1.2; Redmi 5A MIUI/V9.2.4.0.NCKMIEK)',
        'Dalvik/1.6.0 (Linux; U; Android 4.4.4; 2014818 MIUI/V6.4.4.0.KHJMICB)',
        'Mozilla/5.0 (Linux; Android 8.0.0; SM-G965F Build/R16NW; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/69.0.3497.100 Mobile Safari/537.36',
        'Dalvik/2.1.0 (Linux; U; Android 8.1.0; Redmi Note 5 MIUI/V9.5.13.0.OEIMIFA)',
        'Dalvik/2.1.0 (Linux; U; Android 8.0.0; SHIELD Android TV Build/OPR6.170623.010)'
    ];

    /**
     * CURL wrapper
     *
     * @param array $params
     *     @option string  "method" [GET/POST; default: GET]
     *     @option string  "url" [request URL]
     *     @option string  "post_fields" [fields encoded http_build_query(); for POST method]
     *     @option string  "user_agent"   [default: Curl]
     *     @option string  "follow" [follow location redirects; default: 1]
     *     @option string  "max_redirects"
     *     @option string  "encoding" [default none]
     *     @option string  "header" [follow location redirects] // TODO like https://www.twilio.com/docs/libraries/php/custom-http-clients-php
     *     @option string  "ssl" [verify SSL]
     *     @option string  "timeout" [default const PARAM_HTTP_TIMEOUT]
     *     @option string  "return_transfer" [return response body; default: 1]
     *     @option string  "proxy" [proxy connection params]
     *     @option string  "proxy" => "tunnel" ['CONNECT HTTP' method; make TCP socket with handshake
     *          to destination server directly through a given HTTP proxy)
     *     @option string  "referer" [follow location redirects]
     *     @option string  "cookie" [follow location redirects]
     *     @option string  "userpwd" [HTTP Auth string - login:password]
     *     @option string  "convert" [encodings array('from', 'to')]
     *     @option boolean "debug" [dump debugging info]
     *
     * @return string|null
     */
    public static function getUrlContent($params = [])
    {
        if (is_array($params)) {
            $ch = curl_init();

            if (isset($params['method']) && $params['method'] == 'POST') {
                curl_setopt($ch, CURLOPT_POST, 1);
            } else {
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
            }

            if (isset($params['user_agent'])) {
                curl_setopt($ch, CURLOPT_USERAGENT, $params['user_agent']);
            } else {
                curl_setopt($ch, CURLOPT_USERAGENT, 'Curl');
            }

            if (isset($params['follow'])) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $params['follow']);
            } else {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            }

            if (isset($params['max_redirects'])) {
                curl_setopt($ch, CURLOPT_MAXREDIRS, intval($params['max_redirects']));
            }

            if (isset($params['encoding'])) {
                curl_setopt($ch, CURLOPT_ENCODING, '');
            }

            if (isset($params['header'])) {
                curl_setopt($ch, CURLOPT_HEADER, 1);
            }

            if (isset($params['ssl'])) {
                if ( ! is_bool($params['ssl'])) {
                    throw new \InvalidArgumentException("SSL enabling param must be boolean.");
                }
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $params['ssl']);
            }

            if (isset($params['timeout'])) {
                if (defined('CURLOPT_TIMEOUT_MS')) {
                    $opts[CURLOPT_TIMEOUT_MS] = ceil($params['timeout'] * 1000);
                } else {
                    $opts[CURLOPT_TIMEOUT] = ceil($params['timeout']);
                }
            } else {
                curl_setopt($ch, CURLOPT_TIMEOUT, self::PARAM_HTTP_TIMEOUT);
            }

            // The defined()s are here as the *_MS opts are not available on older
            // cURL versions
            if (isset($params['connect_timeout'])) {
                if (defined('CURLOPT_CONNECTTIMEOUT_MS')) {
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, ceil($params['connectTimeout'] * 1000));
                } else {
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, ceil($params['connectTimeout']));
                }
            }

            if (isset($params['return_transfer'])) {
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, $params['return_transfer']);
            } else {
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            }

            curl_setopt($ch, CURLOPT_URL, $params['url']);

            if (isset($params['post_fields'])) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params['post_fields']);
            }

            if (isset($params['cookie'])) {
                curl_setopt($ch, CURLOPT_COOKIE, $params['cookie']);
            }

            if (isset($params['headers']) && is_array($params['headers'])) {
                /*
                if (self::checkCurlVersion() === 'old') {
                    $header = [];
                    foreach ($params['sendHeader'] as $k => $v)
                    {
                        $header[] = $k. ': ' . $v . "\r\n";
                    }
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                } else {*/
                curl_setopt($ch, CURLOPT_HTTPHEADER, $params['headers']);
            }

            if (isset($params['referer'])) {
                curl_setopt($ch, CURLOPT_REFERER, $params['referer']);
//            } else {
//                curl_setopt($ch, CURLOPT_REFERER, 'https://www.google.com/');
            }

            /**
             * Basic auth.
             * example userpwd:
             *      username:password123
             */
            if (isset($params['userpwd'])) {
                curl_setopt($ch, CURLOPT_USERPWD, $params['userpwd']);
            }

            if (isset($params['proxy'])) {
                if (isset($params['proxy']['tunnel'])) {
                    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $params['proxy']['tunnel']);
                }
                if (isset($params['proxy']['type'])) {
                    if ($params['proxy']['type'] === 'SOCKS5') {
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                    } elseif ($params['proxy']['type'] === 'HTTP') {
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    }
                }
                if (isset($params['proxy']['address'])) {
                    curl_setopt($ch, CURLOPT_PROXY, $params['proxy']['address']);
                    if (isset($params['proxy']['auth'])) {
                        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $params['proxy']['auth']);
                    }
                }
            }

            if (isset($params['debug'])) {
                curl_setopt($ch, CURLOPT_VERBOSE, true);
            }
            $result = curl_exec($ch);
            if (isset($params['debug']) && ! curl_errno($ch)) {
                print_r($info = curl_getinfo($ch));
            }
            curl_close($ch);

            if (isset($params['convert'])) {
                $result = iconv($params['convert'][0], $params['convert'][1], $result);
            }
            return $result;
        }
        return null;
    }

    public static function fileGetContents($url)
    {
        if ($ch = curl_init()) {
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_ENCODING, 'utf-8'); // gzip ?
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $data = curl_exec($ch);
            curl_close($ch);

            return $data;
        } else {
            return 0;
        }
    }

    /**
     * @param $url
     * @return int
     */
    public static function getStatusCode($url)
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'HEAD');    // good idea?
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);    // good idea?

        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_HEADER, true);
        curl_setopt($curlHandle, CURLOPT_FRESH_CONNECT, true);  // don't use cached connection
        curl_setopt($curlHandle, CURLOPT_USERAGENT, self::$uaDesktops[0]);
        curl_setopt($curlHandle, CURLOPT_NOBODY, true);

        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 5);
//        curl_setopt($curlHandle, CURLOPT_BUFFERSIZE, 128); // don't use! can pause process on all timeout time
        curl_setopt($curlHandle, CURLOPT_NOPROGRESS, false);

        $headers = ["Cache-Control: no-cache"];
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($curlHandle, CURLOPT_PROGRESSFUNCTION, function(
            $downloadSize, $downloaded, $uploadSize, $uploaded
        ) {
            $fileSizeLimit = 10 * 1024 * 1024;
            // If $Downloaded exceeds 1KB, returning non-0 breaks the connection!
            return ($downloaded > $fileSizeLimit) ? 1 : 0;
        });
        curl_exec($curlHandle);
        $responseCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle); // Don't forget to close the connection

        return intval($responseCode);
    }

    public static function checkCurl()
    {
        if (in_array('curl', get_loaded_extensions())) {
            return true;
        } else {
            return false;
        }
    }

    public static function getRedirectedUrl($url)
    {
        if (empty($url)) {
            return false;
        }
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);

        $userAgents = self::$uaMobileAndroid;
        $headers = [
            "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5",
            "Accept-Language: ru-ru,ru;q=0.7,en-us;q=0.5,en;q=0.3",
            "Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7",
            "Keep-Alive: 300"
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgents[mt_rand(0, count($userAgents) - 1)]);
        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if (! curl_errno($ch)) {
            $info = curl_getinfo($ch);
            curl_close($ch);
            return $info['url'];
        } else {
            return false;
        }
    }
}
