<?php

namespace Modules\Core\Packages\SMS;

class RouteSms implements SmsGetWay
{
    protected $username;
    protected $password;
    protected $type;
    protected $dlr;
    protected $source;

    public function __construct()
    {
        $this->username = config("services.sms.route_sms.username");
        $this->password = config("services.sms.route_sms.password");
        $this->type = config("services.sms.route_sms.type");
        $this->dlr = config("services.sms.route_sms.dlr");
        $this->source = config("services.sms.route_sms.source");
    }

    public function send($message, $phone)
    {
        try {
            $data = [
                "username" => $this->username,
                "password" => $this->password,
                "source" => $this->source,
                "type" => $this->type,
                "dlr" => $this->dlr,
                "message" => __('authentication::api.register.messages.code_send', ["code" => $message], 'en'),
                "destination" => $phone, //"96594971095",
            ];
            return $this->request($data);
        } catch (\Exception $e) {
            return ["Result" => "false"];
        }
    }

    public function request($data)
    {
        $ch = curl_init();
        $query = http_build_query($data);
        $url = "http://api.rmlconnect.net/bulksms/bulksms?$query";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        return $this->parse($result);
    }

    public function parse($result)
    {
        $result = str_replace(["\n", "\r", "\t"], '', $result);
        $result = trim(str_replace('"', "'", $result));
        $result = explode('|', $result);
        $r['status_code'] = $result[0];
        $r['mobile'] = $result[1];
        $r['message_id'] = $result[2];
        $r['Result'] = $r['status_code'] == '1701'; //1701 =>Success
        logger('::route-sms-result::');
        logger($r);
        return $r;
    }
}
