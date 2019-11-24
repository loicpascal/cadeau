<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CookieService
{
    /**
     * @param string $name
     * @param null $value
     * @param int $duration en secondes, 1 an par défaut
     */
    public static function setCookie($name = '', $value = null, $duration = 31536000) {
        $response = new Response();
        $cookie = new Cookie($name, $value, time() + $duration);
        $response->headers->setCookie($cookie);
        $response->send();
    }
}