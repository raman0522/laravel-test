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

    public function send_mail($type,$email,$from_email,$from_name,$otp=null)
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
}
