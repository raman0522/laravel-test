<?php

namespace App\Traits;

trait Reply
{
    public function success($msg, $data = null , array $extra = null)
    {
        $info['status_code'] = '1';
        $info['status_text'] = 'success';
        $info['message'] = $msg;
        if($data != null)
        {
            $info['data'] = $data;
        }

        if($extra != null)
        {
            foreach ($extra as $key => $value) 
            {
                $info[$key] = $value;
            }
        }

        return $info;
    }

    public function failed($msg)
    {
        $info['status_code'] = '0';
        $info['status_text'] = 'failed';
        $info['message'] = $msg;
        return $info;
    }

    public function saveImage($request,$key)
    {
        $image = $request->file($key);
        $name = time().'.'.$image->getClientOriginalExtension();
        $destinationPath = public_path('/image');
        $image->move($destinationPath, $name);
        $imageurl = 'image/'.$name;
        return $imageurl;
    }
    # Due to account under review the sendgrid is not wokring otherwise code ois working
    public function send_mail2($type,$email,$from_email,$from_name,$otp=null)
    {

        $subject = $type == 'signup' ? 'SignUp!' : 'Register Otp!';
        $post_data = '{
            "personalizations": [
                {
                    "to": [
                        {
                            "email": "'.$email.'"
                        }
                    ],
                    "subject": "'.$subject.'"
                }
            ],
            "content": [
                {
                    "type": "text/html",
                    "value": "'.$this->mail_content($type,$otp).'"
                }
            ],
            "from": {
                "email": "'.$from_email.'",
                "name": "'.$from_name.'"
            },
        }';

        # Start Curl Request
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.sendgrid.com/v3/mail/send',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $post_data,
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer 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',
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $http_status;
    }

    protected function mail_content($type,$otp = null)
    {
        if($type == 'signup')
        {
            return "<h1>SignUp Link to redirect user will be shown here</h1><p>Currently I am Just making the apis so no link will be made. </p><h4>Use Base register api for further flow</h4>";
        }
        
        return "<h1>Your Register Otp Is : ".$otp."</h1><p>Use register api to register successfully</p>";
    }

    public function send_mail($type,$email,$from_email,$from_name,$otp=null)
    {
        $subject = $type == 'signup' ? 'SignUp!' : 'Register Otp!';

            $url = 'https://api.mailjet.com/v3.1/send';

           $body = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => 'ramandeep.singh.goteso@gmail.com',
                            'Name' => 'Ramandeep'
                        ],
                        'To' => [
                            [
                                'Email' => $email,
                            ]
                        ],
                        'Subject' => $subject,
                        'TextPart' => "Dear passenger 1, welcome to Mailjet! May the delivery force be with you!",
                        'HTMLPart' => $this->mail_content($type,$otp),
                    ]
                ]
            ];

            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($body));
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERPWD,'e253a8220b96ac4c94db9c9e9d98269d:315b0f363fcea9737d61d71c51b43517');
            $response = curl_exec($ch);
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErrorNumber = curl_errno($ch);
            $curlErrorMessage = curl_error($ch);
            curl_close($ch);
            return $responseCode;
            // return array('response' => $response, 'code' => $responseCode);

    }
   

}


