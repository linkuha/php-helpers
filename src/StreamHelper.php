<?php
/**
 * Created by PhpStorm.
 * User: linkuha (Pudich Aleksandr)
 * Date: 04.09.2019
 * Time: 1:21
 */

namespace SimpleLibs;


class StreamHelper
{
    /**
     * @param string $url
     * @param string $certPathCA
     * @return bool
     */
    public static function getStatusCode($url = '', $certPathCA = '') {

        if ("" === $url or ! is_string($url)) {
            return false;
        }
        if (($url = parse_url($url)) === false) {
            return false;
        }
        $url = array_map('trim', $url);
        if (! isset($url['port'])) {
            $url['port'] = $url['scheme'] === 'https' ? 443 : 80;
        } else {
            $url['port'] = (int) $url['port'];
        }
        $path = (isset($url['path'])) ? $url['path'] : '';

        if ($path == '') {
            $path = '/';
        }
        $path .= (isset($url['query'])) ? "?$url[query]" : '';

        if (isset($url['host']) && $url['host'] != gethostbyname($url['host'])) {
            if (PHP_VERSION >= 5) {
                $sslOptions = [
                    'allow_self_signed' => true,
                    'verify_peer' => true,
                    'verify_peer_name' => true,
//                        'crypto_method' => STREAM_CRYPTO_METHOD_TLS_CLIENT,
//                        'crypto_method' => STREAM_CRYPTO_METHOD_SSLv23_CLIENT, // SSLv2 and SSLv3 vulnerable
                    'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT |
                        STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
                ];
                if (! empty($certPathCA) && is_readable($certPathCA)) {
                    $sslOptions['cafile'] = $certPathCA;
                }
                stream_context_set_default([
                    'ssl' => $sslOptions,
                    'http' => [
                        'method' => 'HEAD'
                    ]
                ]);
                // SSL3_GET_RECORD:wrong version number
                // if port not acceptable to scheme
                $headers = get_headers("$url[scheme]://$url[host]:$url[port]$path", 0);
//                $responseCode = substr($headers[0], 9, 3);
            } else {
                $sslPrefix = '';
                if ($url['scheme'] === 'https') {
                    $sslPrefix = 'ssl://';   // tcp transport level by default
                }
                $fp = fsockopen($sslPrefix . $url['host'], $url['port'], $errno, $errstr, 30);
                if (! $fp) {
                    return 0;
                }
                fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
                $headers = fread($fp, 128);
                fclose($fp);
            }
            $headers = (is_array($headers)) ? implode("\n", $headers) : $headers;
            preg_match('#^HTTP/.*\s+(?P<code>\d{3})+\s#i', $headers, $matches);
            if (isset($matches['code'])) {
                return intval($matches['code']);
            }
        }
        return 0;
    }
}
