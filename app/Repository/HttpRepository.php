<?php

namespace App\Repository;

use App\Core\Repository;

class HttpRepository extends Repository
{
    protected const ERRORS = [
        'urlNotFound' => 'URL не задан',
        'connectError' => 'Ошибка: %s',
    ];

    public function get()
    {
        $url = $this->getParam('url');
        
        if (!isset($url)) {
            $this->setError(self::ERRORS['urlNotFound']);

            return;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlErrno > 0) {
            $this->setError(sprintf(self::ERRORS['connectError'], $curlError));
            
            return;
        }

        return $data;
    }

    public function send(array $params = []): void
    {
        //
    }
}
