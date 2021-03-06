<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class HlalaController extends Controller
{
    public function index()
    {
        dd(now()->addMinute(10));
        $access_token = $this->getHualalaToken();
        $secret = 'YJV3nQMt';
        $ordder_list = $this->getOrderList($access_token,$secret);
        dd($ordder_list);
    }

    public function getHualalaToken()
    {
        $token = cache('hualala_token');
        if(isset($token) && !empty($token)) return $token;

        $http = new Client([
            'headers' => [
                'Authorization'=>'Basic ' .base64_encode('guozl:NhzDcu46ert2Nbv'),
            ],
        ]);

        $post_data=['form_params'=>['grant_type'=>'client_credentials','scope'=>'read']];

        $url = 'http://auth.hualala.com/oauth/token?grant_type=client_credentials&scope=read';
        //$url = 'http://dohko.auth.hualala.com/oauth/token';

        $respon = $http->request('POST',$url,$post_data);


        $res = json_decode($respon->getBody(),true);

        if(!isset($res['access_token']) || empty($res['access_token'])) return $this->error('获取token失败');

        cache('hualala_token',$res['access_token']);
        return $res['access_token'];

        //dGlhbm1pbWk6aFFXMUVGRUZZMjNTSGI=
        //dd(base64_encode('guozl:NhzDcu46ert2Nbv'));
//        $request = [
//            'grant_type'=>'client_credentials',
//            'scope'=>'read'
//        ];
//
//        $post_data = http_build_query($request);
//        $url = "http://auth.hualala.com/oauth/token";
//
//        $headers = ['Authorization: Basic Z3Vvemw6Tmh6RGN1NDZlcnQyTmJ2='];
//
//        $request = [
//            'grant_type'=>'client_credentials',
//            'scope'=>'read'
//        ];
//
//        $ch = curl_init();
//
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
//
//         post数据
//        curl_setopt($ch, CURLOPT_POST, 1);
//         post的变量
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//
//        $output = curl_exec($ch);
//        curl_close($ch);
//
//        dd($output);
    }

    public function getOrderList($token,$secret)
    {
        $url = 'http://open-api.hualala.com/card/queryCardInfo';

        $http = new Client([
            'headers' => [
                'Authorization'=>'bearer ' .$token,
            ],
        ]);

        $time = time();

        $should_data = [
            'timestamp' => $time,//时间戳,用户签名校验
            'groupID' =>31457,//集团 ID
            'shopID' => 76075780,//店铺 ID
            'sourceWay' => 1,//请求来源，1
            'sourceType' => 30,//会员来源类型 81
            'cardTypeID' => '800330942903242752',//卡类型 ID
        ];

        $sign = $this->getSign($should_data,$secret);

        $post_data=[
            'form_params'=>[
                'timestamp' => $time,//时间戳,用户签名校验
                'groupID' =>31457,//集团 ID
                'shopID' => 76075780,//店铺 ID
                'sourceWay' => 1,//请求来源，1
                'sourceType' => 30,//会员来源类型 81
                'cardTypeID' => 800330942903242752,//卡类型 ID
                'signature'=>$sign,
            ]
        ];

        $respon = $http->request('POST',$url,$post_data);

        return json_decode($respon->getBody(),true);

    }

    public function getSign($data,$secret)
    {
        ksort($data);
        $tmp_str = '';
        foreach ($data as $k=>$val){
            $tmp_str .= $k.$val;
        }

        $str = 'key'.$tmp_str.'userKey'.$secret.'secret';

        $shar = sha1($str);

        $sign=strtoupper($shar);

        return $sign;

    }
}

