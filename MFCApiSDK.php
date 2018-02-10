<?php

    class MFCApiSDK {

        const Host = 'https://myfigurecollection.net';
        const FileName = 'api';
        const Version = 4;

        const TimeOut = 10;

        private $publicKey, $privateKey, $publicToken, $privateToken;


        /**
         * MFCApiSDK constructor.
         *
         * @param string $publicKey
         * @param string $privateKey
         */
        function __construct($publicKey, $privateKey) {

            $this->publicKey = $publicKey;
            $this->privateKey = $privateKey;
            $this->publicToken = '';
            $this->privateToken = '';

        }


        /**
         * @param string $publicToken
         * @param string $privateToken
         *
         * @return MFCApiSDK
         */
        function setToken($publicToken, $privateToken) {

            $this->publicToken = $publicToken;
            $this->privateToken = $privateToken;

            return $this;

        }


        /**
         * @param string $request
         * @param mixed[] $params
         *
         * @return mixed[]
         */
        function call($request, $params = []) {

            $reply = [];

            $params = array_merge([

                'publicKey' => $this->publicKey,
                'request' => $request,
                'requestSignature' => hash_hmac('sha256', $request, $this->privateKey)

            ], $params);

            if ($this->publicToken !== '' && $this->privateToken !== '') {

                $params['publicToken'] = $this->publicToken;
                $params['tokenSignature'] = hash_hmac('sha256', '_token_', $this->privateToken);

            }

            foreach ($params as $paramName => $paramValue) {

                $params[$paramName] = sprintf('%s=%s', $paramName, urlencode($paramValue));

            }

            $getURI = sprintf('%s/%s.v%s.php?%s', self::Host, self::FileName, self::Version, implode('&', $params));


            $ch = curl_init($getURI);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::TimeOut);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::TimeOut);

            $apiReply = curl_exec($ch);

            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpStatus == 200) {

                $reply = json_decode($apiReply, true);

            }

            return $reply;

        }

    }