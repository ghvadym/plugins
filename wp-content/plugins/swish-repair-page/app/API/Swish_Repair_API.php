<?php


class Swish_Repair_API
{
    const SWISH_SANDBOX_URL = 'https://staging.getswish.pub.tds.tieto.com/cpc-swish/api/v1/paymentrequests';
    const SWISH_LIVE_URL = 'https://cpc.getswish.net/swish-cpcapi/api/v1/paymentrequests';

    private static array $headerInfo = [];

    static function api_request(array $data = [])
    {
        $sandboxMode = get_option('swish_sandbox_mode');
        $swishPayeeAlias = get_option('swish_payee_alias');

        $data = array_merge([
            'payeeAlias' => '1232838936',
            'currency'   => 'SEK'
        ], $data);

        if ($sandboxMode) {
            $url = self::SWISH_SANDBOX_URL;
        } else {
            $url = self::SWISH_LIVE_URL;
            $data['payeeAlias'] = $swishPayeeAlias;
        }

        $data_string = json_encode($data);

        $header = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string),
        ];

        $curl = curl_init();

        $params = [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => 1,
            CURLOPT_SSL_VERIFYHOST => '1',
            CURLOPT_SSL_VERIFYPEER => '1',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POSTFIELDS     => $data_string,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_HEADER         => 1,
            CURLOPT_HTTPHEADER     => $header,
            CURLOPT_HEADERFUNCTION => function ($curl, $header) use (&$headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) {
                    return $len;
                }

                $name = strtolower(trim($header[0]));
                self::$headerInfo[$name] = trim($header[1]);

                return $len;
            }
        ];

        $params += self::assign_certificates();

        curl_setopt_array($curl, $params);

        curl_exec($curl);

        if (curl_errno($curl)) {
            return curl_error($curl);
        }

        curl_close($curl);

        return self::$headerInfo;
    }

    static function assign_certificates(): array
    {
        $sandboxMode = get_option('swish_sandbox_mode');
        $files = [];

        if (!$sandboxMode) {
            //Production
            $crtFile = SWISH_RP_DIR . 'certificates/production/swish_certificate.pem';
            $keyFile = SWISH_RP_DIR . 'certificates/production/private.key';
        } else {
            //Sandbox
            $crtFile = SWISH_RP_DIR . 'certificates/sandbox/Invistic_merchant.crt.pem';
            $keyFile = SWISH_RP_DIR . 'certificates/sandbox/Invistic_merchant.key.pem';
        }

        if (file_exists($crtFile)) {
            $files[CURLOPT_SSLCERT] = $crtFile;
        }

        if (file_exists($keyFile)) {
            $files[CURLOPT_SSLKEY] = $keyFile;
        }

        return $files;
    }

    static function paid_statuses(): array
    {
        return [452, 456, 462, 467];
    }
}