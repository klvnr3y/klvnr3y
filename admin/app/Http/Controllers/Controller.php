<?php

namespace App\Http\Controllers;

use App\EmailTemplate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Ixudra\Curl\Facades\Curl;


use JoliCode\Slack\ClientFactory;
use JoliCode\Slack\Exception\SlackErrorResponse;

/**
 * @OA\Info(
 *    title="Promise Network Gift Cards API",
 *    version="1.0.0",
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function strip_quotes_from_message($message_body)
    {
        $els_to_remove = [
            'blockquote',                           // Standard quote block tag
            'div.moz-cite-prefix',                  // Thunderbird
            'div.gmail_extra', 'div.gmail_quote',   // Gmail
            'div.yahoo_quoted'                      // Yahoo
        ];

        $dom = new \PHPHtmlParser\Dom;
        $dom->load($message_body);

        foreach ($els_to_remove as $el) {
            $founds = $dom->find($el)->toArray();
            foreach ($founds as $f) {
                $f->delete();
                unset($f);
            }
        }

        // Outlook doesn't respect
        // http://www.w3.org/TR/1998/NOTE-HTMLThreading-0105#Appendix%20B
        // We need to detect quoted replies "by hand"
        //
        // Example of Outlook quote:
        //
        // <div>
        //      <hr id="stopSpelling">
        //      Date: Fri. 20 May 2016 17:40:24 +0200<br>
        //      Subject: Votre facture Selon devis DEV201605201<br>
        //      From: xxxxxx@microfactures.com<br>
        //      To: xxxxxx@hotmail.fr<br>
        //      Lorem ipsum dolor sit amet consectetur adipiscing...
        // </div>
        //
        // The idea is to delete #stopSpelling's parent...

        $hr  = $dom->find('#stopSpelling', /*nth result*/ 0);
        if (null !== $hr) {
            $hr->getParent()->delete();
        }

        // Roundcube adds a <p> with a sentence like this one, just
        // before the quote:
        // "Le 21-05-2016 02:25, AB Prog - Belkacem Alidra a écrit :"
        // Let's remove it
        $pattern = '/Le [0-9]{2}-[0-9]{2}-[0-9]{4} [0-9]{2}:[0-9]{2}, [^:]+ a &eacute;crit&nbsp;:/';
        $ps = $dom->find('p')->toArray();
        foreach ($ps as $p) {
            if (preg_match($pattern, $p->text())) {
                $p->delete();
                unset($p);
            }
        }

        // Let's remove empty tags like <p> </p>...
        $els = $dom->find('p,span,b,strong,div')->toArray();
        foreach ($els as $e) {
            $html = trim($e->innerHtml());
            if (empty($html) || $html == "&nbsp;") {
                $e->delete();
                unset($e);
            }
        }

        return $dom->root->innerHtml();
    }

    public function insertClearentLog($api_endpoint, $log)
    {
        \Log::channel('clearent')->info($api_endpoint);
        \Log::channel('clearent')->info($log);
    }
    public function getClearentBoardingAPI($api_endpoint, $merchantNumber = null)
    {
        $url = (env("CLEARENT_ENV") == 'sandbox' ? 'https://boarding-sb' : 'https://boarding') . '.clearent.net/api/' . $api_endpoint;
        $response = Curl::to($url)
            ->withHeader('Content-Type: application/json')
            ->withHeader('AccessKey: ' . (env('CLEARENT_ENV') == 'sandbox' ? env('CLEARENT_ACCESS_KEY_SANDBOX') : env('CLEARENT_ACCESS_KEY_PROD')))
            ->withHeader('ExchangeID: ' . time())
            ->withHeader('MerchantID: ' . $merchantNumber = null ? env('CLEARENT_HNK') : $merchantNumber)
            ->withHeader('Expect: ')
            ->get();
        $this->insertClearentLog($url, $response);
        return json_decode($response, true);
    }

    public function postClearentBoardingAPI($api_endpoint, $data, $merchantNumber = null)
    {

        $merchantId = $merchantNumber == null ? env('CLEARENT_HNK') : $merchantNumber;
        $url = (env("CLEARENT_ENV") == 'sandbox' ? 'https://boarding-sb' : 'https://boarding') . '.clearent.net/api/' . $api_endpoint;
        $response = Curl::to($url)
            ->withData(json_encode($data))
            ->withHeader('Content-Type: application/json')
            ->withHeader('AccessKey: ' . (env('CLEARENT_ENV') == 'sandbox' ? env('CLEARENT_ACCESS_KEY_SANDBOX') : env('CLEARENT_ACCESS_KEY_PROD')))
            ->withHeader('ExchangeId: ' . time())
            ->withHeader('MerchantId: ' . $merchantId)
            ->withHeader('Expect: ')
            ->post();
        $this->insertClearentLog($url, $response);
        $this->insertClearentLog('merchantNumber', $merchantNumber);
        $this->insertClearentLog('accessKey', (env('CLEARENT_ENV') == 'sandbox' ? env('CLEARENT_ACCESS_KEY_SANDBOX') : env('CLEARENT_ACCESS_KEY_PROD')));

        return json_decode($response, true);
    }


    public function deleteClearentBoardingAPI($api_endpoint, $merchantNumber = null)
    {

        $merchantId = $merchantNumber == null ? env('CLEARENT_HNK') : $merchantNumber;
        $url = (env("CLEARENT_ENV") == 'sandbox' ? 'https://boarding-sb' : 'https://boarding') . '.clearent.net/api/' . $api_endpoint;
        $response = Curl::to($url)
            ->withHeader('Content-Type: application/json')
            ->withHeader('AccessKey: ' . (env('CLEARENT_ENV') == 'sandbox' ? env('CLEARENT_ACCESS_KEY_SANDBOX') : env('CLEARENT_ACCESS_KEY_PROD')))
            ->withHeader('ExchangeId: ' . time())
            ->withHeader('MerchantId: ' . $merchantId)
            ->withHeader('Expect: ')
            ->delete();
        $this->insertClearentLog($url, $response);
        return json_decode($response, true);
    }

    public function putClearentBoardingAPI($api_endpoint, $data, $merchantNumber = null)
    {

        $merchantId = $merchantNumber == null ? env('CLEARENT_HNK') : $merchantNumber;
        $url = (env("CLEARENT_ENV") == 'sandbox' ? 'https://boarding-sb' : 'https://boarding') . '.clearent.net/api/' . $api_endpoint;
        $response = Curl::to($url)
            ->withData(json_encode($data))
            ->withHeader('Content-Type: application/json')
            ->withHeader('AccessKey: ' . (env('CLEARENT_ENV') == 'sandbox' ? env('CLEARENT_ACCESS_KEY_SANDBOX') : env('CLEARENT_ACCESS_KEY_PROD')))
            ->withHeader('ExchangeId: ' . time())
            ->withHeader('MerchantId: ' . $merchantId)
            ->withHeader('Expect: ')
            ->put();
        $this->insertClearentLog($url, $response);
        return json_decode($response, true);
    }

    public function generateMerchantNumber()
    {
        $data = (object)[
            "hierarchyNodeKey" => env('CLEARENT_HNK')
        ];
        $merchantNumber = $this->postClearentBoardingAPI('boardingmanagement/v1.0/applications/create', $data);
        // echo json_encode($data);
        // echo json_encode($merchantNumber);
        // return $merchantNumber;
        $merchantNumber = $merchantNumber['merchantNumber'];
        return $merchantNumber;
    }

    public function uploadMerchantFile($user_id, $category, $file, $file_name = '')
    {
        // echo $user_id;
        // $user_id = $request->user_id;
        // $file = $request->file('file');
        $file_name = $file_name != '' ? $file_name  : $file->getClientOriginalName();

        $user = \App\User::with('user_fields')->find($user_id);
        if ($user) {
            $merchantName = isset($user->user_fields) ?  $user->user_fields->merchant_name : $user->id;
            $merchantName = str_replace(' ', '_', $merchantName);


            if ($category != 'Paysafe Statement') {
                $file_name = $merchantName . $file_name;
            }


            $file_size = $this->bytesToHuman($file->getSize());
            $file_url = $file->storeAs(
                'public',
                time() . '_' . $file_name
            );

            $file_url = str_replace('public/', '', $file_url);


            $data = \App\MerchantFiles::create([
                'user_id' => $user_id,
                'category' => $this->getDocumentCategory($category),
                'file_size' => $file_size,
                'file_name' => $file_name,
                'file_url' => $file_url,
            ]);

            return $data;
        }
    }

    public function bytesToHuman($bytes)
    {
        $units = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function getDocumentCategory($category)
    {
        $categories = [
            7 => "Signed Application",
            12 => "Voided Check",
            13 => "Bank Account Verification Results",
            16 => "Federal Firearms License",
            17 => "Sporting Goods and Firearms Addendum",
            21 => "Driver's License (Owner Identification)",
            23 => "Early Termination Fee Certificate",
            25 => "Site Photo",
            26 => "Inventory Photo",
            28 => "Multi-Location Addendum",
            29 => "MOTO Questionnaire",
            30 => "Personal Guarantee"
        ];

        return isset($categories[$category]) ? $categories[$category] : $category;
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    public function curlPost($url, $data = NULL, $headers = NULL)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);

        if (curl_error($ch)) {
            trigger_error('Curl Error:' . curl_error($ch));
        }

        curl_close($ch);
        return $response;
    }

    public function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }



    public function slackTicketNotif($ticket)
    {
        $client = ClientFactory::create('xoxb-2831012795332-2828770769779-7Kj3tFiW1WZqmnRCfXR9XDQE');

        try {
            // This method requires your token to have the scope "chat:write"

            $response = $client->chatPostMessage([
                'channel' => 'ticket-notif',
                'text' => 'Message with blocks',
                'blocks' => json_encode([
                    [
                        "type" => "section",
                        "text" => [
                            "type" => "plain_text",
                            "text" => '',
                            "emoji" => true
                        ]
                    ],
                    [
                        "type" => "actions",
                        "elements" => [
                            [
                                "type" => "button",
                                "text" => [
                                    "type" => "plain_text",
                                    "emoji" => true,
                                    "text" => "Visit"
                                ],
                                "url" => "https://promise.network/tickets/ticket/" . $ticket->id . ""
                            ],
                            [
                                "type" => "button",
                                "text" => [
                                    "type" => "plain_text",
                                    "emoji" => true,
                                    "text" => "Mark as Resolved"
                                ],
                                "style" => "primary",
                                "url" => "https://promise.network/tickets/ticket/" . $ticket->id . "?status=Closed"
                            ],
                            [
                                "type" => "button",
                                "text" => [
                                    "type" => "plain_text",
                                    "emoji" => true,
                                    "text" => "Ignore"
                                ],
                                "style" => "danger",
                                "url" => "https://promise.network/tickets/ticket/" . $ticket->id . "?status=Archive"
                            ]
                        ]
                    ]
                ]),
            ]);

            \Log::info('slack Messages sent.');;
        } catch (SlackErrorResponse $e) {
            \Log::info('Fail to send the message.', PHP_EOL, $e->getMessage());;
        }
    }

    public function sendRequestToPngift($url, $method, $data)
    {
        $url = env('PNGIFT_URL') . "/api" . $url;
        $ch = curl_init($url);



        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: multipart/form-data',
                'Authorization: Bearer ' . env('PNGIFT_API_KEY'),
            ]);
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . env('PNGIFT_API_KEY'),
            ]);
        }
        if ($method == 'UPDATE') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        }
        if ($method == 'DELETE') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        // SET Method as a PUT
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        // dd($result);

        return json_decode($result, true);
    }

    /**
     * @param mixed $id, data
     * 
     * 
     * $data = [
     *      'to_name'   => 'klaven',
     *      'to_email'   => 'admin@test.com',
     * ];
     * 
     * others are optional
     * 
     */

    public function setup_email_template($title, $data)
    {
        $email_template = EmailTemplate::where('title', $title)->first();

        $to_name = !empty($data['to_name']) ? $data['to_name'] : "";
        $by_name = !empty($data['by_name']) ? $data['by_name'] : "";
        $to_email = !empty($data['to_email']) ? $data['to_email'] : "";
        $from_name = !empty($data['from_name']) ? $data['from_name'] : env("MIX_APP_NAME");
        $from_email = !empty($data['from_email']) ?  $data['from_email'] : "bfss@5ppsite.com";
        $username = !empty($data['username']) ? $data['username'] : "";
        $site_name = !empty($data['site_name']) ? $data['site_name'] : env("MIX_APP_NAME");
        $link_origin = !empty($data['link_origin']) ? $data['link_origin'] : "";
        $link = !empty($data['link']) ? $data['link'] : '';
        $token = !empty($data['token']) ? $data['token'] : "";
        $template = !empty($data['template']) ?  $data['template'] : 'admin.emails.email-template';
        $subject = "";
        $body = "";

        if (!empty($data['subject'])) {
            $subject = $data['subject'];
        } else {
            $subject = $email_template->subject;
            $subject = str_replace('[user:display-name]', $by_name ? $by_name : $to_name, $subject);
            $subject = str_replace('[site:name]', $site_name, $subject);
        }

        if ($title == 'FORGOT / CHANGE PASSWORD') {
            $a_link = '<a
            href="' . $link . '" class="es-button" target="_blank"
            style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:roboto, "helvetica neue", helvetica, arial, sans-serif;font-size:18px;color:#333333;border-style:solid;border-color:#FEC300;border-width:10px 20px;display:inline-block;background:#FEC300;border-radius:4px;font-weight:bold;font-style:normal;line-height:22px;width:auto;text-align:center;">Click to Set Up Password</a>';

            $a_link_origin = '<a
            href="' . $link_origin . '" class="es-button" target="_blank"
            style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:roboto, "helvetica neue", helvetica, arial, sans-serif;font-size:18px;color:#333333;border-style:solid;border-color:#FEC300;border-width:10px 20px;display:inline-block;background:#FEC300;border-radius:4px;font-weight:bold;font-style:normal;line-height:22px;width:auto;text-align:center;">CCG Login</a>';

            $body = $email_template->body;
            $body = str_replace('[user:display-name]', $to_name, $body);
            $body = str_replace('[user:one-time-login-url]', $a_link, $body);
            $body = str_replace('[site:login-url]', $a_link_origin, $body);
            $body = str_replace('[user:account-name]', $username, $body);
        } else if ($title == 'Registration - Success') {
            $a_link = '<a
            href="' . $link . '" class="es-button" target="_blank"
            style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:roboto, "helvetica neue", helvetica, arial, sans-serif;font-size:18px;color:#333333;border-style:solid;border-color:#FEC300;border-width:10px 20px;display:inline-block;background:#FEC300;border-radius:4px;font-weight:bold;font-style:normal;line-height:22px;width:auto;text-align:center;">Click to Set Up Password</a>';

            $a_link_origin = '<a
            href="' . $link_origin . '" class="es-button" target="_blank"
            style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:roboto, "helvetica neue", helvetica, arial, sans-serif;font-size:18px;color:#333333;border-style:solid;border-color:#FEC300;border-width:10px 20px;display:inline-block;background:#FEC300;border-radius:4px;font-weight:bold;font-style:normal;line-height:22px;width:auto;text-align:center;">CCG Login</a>';

            $body = $email_template->body;
            $body = str_replace('[user:display-name]', $to_name, $body);
            $body = str_replace('[user:one-time-login-url]', $a_link, $body);
            $body = str_replace('[site:login-url]', $a_link_origin, $body);
            $body = str_replace('[user:account-name]', $username, $body);
        } else if ($title == 'Password Change') {
            $body = $email_template->body;
            $body = str_replace('[user:display-name]', $to_name, $body);
        } else if ($title == 'Ticketing - Initial Ticket') {
            $body = $email_template->body;
            $body = str_replace('[user:field_first_name]', $to_name, $body);
        } else if ($title == 'Ticketing - Ticket Resolved') {
            $body = $email_template->body;
            $body = str_replace('[user:field_first_name]', $to_name, $body);
        } else if ($title == 'You Have a New Instant Message Email') {
            $body = $email_template->body;
            $body = str_replace('[user:field_first_name]', $to_name, $body);
        } else if ($title == 'Invite People') {
            $a_link = '<a
            href="' . $link . '" class="es-button" target="_blank"
            style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:roboto, "helvetica neue", helvetica, arial, sans-serif;font-size:18px;color:#333333;border-style:solid;border-color:#FEC300;border-width:10px 20px;display:inline-block;background:#FEC300;border-radius:4px;font-weight:bold;font-style:normal;line-height:22px;width:auto;text-align:center;">Click to Register</a>';

            $body = $email_template->body;
            $body = str_replace('[user:register-url]', $a_link, $body);
            $body = str_replace('[user:field_first_name]', $to_name, $body);
            $body = str_replace('[site:name]', $site_name, $body);
        }

        $data_email = [
            'to_name'       => $to_name,
            'to_email'      => $to_email,
            'subject'       => $subject,
            'from_name'     => $from_name,
            'from_email'    => $from_email,
            'template'      => $template,
            'body_data'     => [
                "content" => $body,
            ]
        ];
        event(new \App\Events\SendMailEvent($data_email));
    }

    /** for stripe */

    /**
     * stripe_customer_charge function
     *
     * @param array $data
     * @return void
     */
    public function stripe_customer_charge($data)
    {
        $stripe = new \Stripe\StripeClient(env("APP_STRIPE_API_KEY"));

        $exp_date = explode('/', $data['card_expiry']);
        $exp_month = $exp_date[0];
        $exp_year = (int) '20' . $exp_date[1];

        // CREATE TOKEN
        $token = $stripe->tokens->create([
            'card' => [
                'number' => $data['credit_card_number'],
                'exp_month' => $exp_month,
                'exp_year' => $exp_year,
                'cvc' => $data['credit_cvv'],
            ],
        ]);

        $token_id = $token->id;

        // SEARCH FOR STRIPE ACCOUNT USING EMAIL AND METADATA
        $customer = $stripe->customers->search([
            'query' => 'email:\'' . $data['email'] . '\' AND metadata[\'app_name\']:\'' . $data['metadata']['app_name'] . '\'',
        ]);

        if ($customer && count($customer->data) > 0) {
            $customer = $stripe->customers->update(
                $customer->data[0]->id,
                [
                    'description' => $data['description'],
                    'name' => $data['firstname'] . ' ' . $data['lastname'],
                    'email' => $data['email'],
                    'address' => [
                        'line1' =>  $data['billing_street_address1'],
                        'line2' => isset($data['billing_street_address2']) ? $data['billing_street_address2'] : "",
                        'city' => $data['billing_city'],
                        'country' => $data['billing_country'],
                        'postal_code' => $data['billing_zip'],
                        'state' => $data['billing_state'],
                    ],
                    'shipping' => [
                        'address' => [
                            'line1' =>  $data['billing_street_address1'],
                            'line2' => isset($data['billing_street_address2']) ? $data['billing_street_address2'] : "",
                            'city' => $data['billing_city'],
                            'country' => $data['billing_country'],
                            'postal_code' => $data['billing_zip'],
                            'state' => $data['billing_state'],
                        ],
                        'name' => $data['firstname'] . ' ' . $data['lastname'],
                        // 'carrier' => '',
                        'phone' => !empty($data['phone_number']) ? $data['phone_number'] : "",
                        // 'tracking_number' => ''
                    ],
                    'source' => $token_id,
                    "metadata" => [
                        "app_name" => $data['metadata']['app_name']
                    ]
                ]
            );
        } else {
            // CREATE CUSTOMER
            $customer = $stripe->customers->create([
                'description' => $data['description'],
                'name' => $data['firstname'] . ' ' . $data['lastname'],
                'email' => $data['email'],
                'address' => [
                    'line1' =>  $data['billing_street_address1'],
                    'line2' => isset($data['billing_street_address2']) ? $data['billing_street_address2'] : "",
                    'city' => $data['billing_city'],
                    'country' => $data['billing_country'],
                    'postal_code' => $data['billing_zip'],
                    'state' => $data['billing_state'],
                ],
                'shipping' => [
                    'address' => [
                        'line1' =>  $data['billing_street_address1'],
                        'line2' => isset($data['billing_street_address2']) ? $data['billing_street_address2'] : "",
                        'city' => $data['billing_city'],
                        'country' => $data['billing_country'],
                        'postal_code' => $data['billing_zip'],
                        'state' => $data['billing_state'],
                    ],
                    'name' => $data['firstname'] . ' ' . $data['lastname'],
                    // 'carrier' => '',
                    'phone' => !empty($data['phone_number']) ? $data['phone_number'] : "",
                    // 'tracking_number' => ''
                ],
                'source' => $token_id,
                "metadata" => [
                    "app_name" => $data['metadata']['app_name']
                ]
            ]);
        }

        $charge = [];
        if ($data['charge_amount'] > 0) {
            $charge = $stripe->charges->create([
                'customer' => $customer->id,
                'receipt_email' =>  $data['email'],
                // 'source' => $token_id,
                'amount' =>  $data['charge_amount'] * 100,
                'currency' => 'usd',
                'description' => $data['charge_description'],
                'shipping' => [
                    'address' => [
                        'line1' => $data['billing_street_address1'],
                        'line2' => !empty($data['billing_street_address2']) ? $data['billing_street_address2'] : "",
                        'city' => $data['billing_city'],
                        'country' => $data['billing_country'],
                        'postal_code' => $data['billing_zip'],
                        'state' => $data['billing_state'],
                    ],
                    // 'email' => 'joshuasaubon@gmail.com',
                    'name' => $data['firstname'] . ' ' . $data['lastname'],
                    'phone' =>  !empty($data['phone_number']) ? $data['phone_number'] : "",
                ],
                [
                    'metadata' => [
                        "app_name" => $data['metadata']['app_name'],
                    ]

                ]
            ]);
        }

        return ['customer' => $customer, 'charge' => $charge];
    }

    /**
     * add / update product
     *
     * @param [type] $data
     * @return void
     */
    public function stripe_product($data)
    {
        $stripe = new \Stripe\StripeClient(env("APP_STRIPE_API_KEY"));

        $product = [];

        if (isset($data['stripe_product_id'])) {
            $product = $stripe->products->update(
                $data['stripe_product_id'],
                [
                    "name" => $data['product_name'],
                    "description" => $data['description'],
                    "metadata" => ["app_name" => $data['metadata']['app_name']]
                ]
            );
        } else {
            $product = $stripe->products->create([
                "name" => $data['product_name'],
                "description" => $data['description'],
                "metadata" => ["app_name" => $data['metadata']['app_name']]
            ]);
        }

        return $product;
    }

    /**
     * add / update price for product
     *
     * @param [type] $data
     * @return void
     */
    public function stripe_price($data)
    {
        $stripe = new \Stripe\StripeClient(env("APP_STRIPE_API_KEY"));

        $price = [];

        if ($data['stripe_product_id']) {
            if (!empty($data['stripe_price_id'])) {
                $price = $stripe->prices->update(
                    $data['stripe_price_id'],
                    [
                        'unit_amount' => $data['amount'] * 100,
                        'currency' => 'usd',
                        'recurring' => ['interval' => $data['type'] == 'Yearly' ? 'year' : 'month'],
                        'product' => $data['stripe_product_id'],
                        'metadata' => ['app_name' => $data['metadata']['app_name']]
                    ]
                );
            } else {
                $price = $stripe->prices->create([
                    'unit_amount' => $data['amount'] * 100,
                    'currency' => 'usd',
                    'recurring' => ['interval' => 'year'],
                    'product' => $data['stripe_product_id'],
                    'metadata' => ['app_name' => $data['metadata']['app_name']]
                ]);
            }
        }

        return $price;
    }

    /**
     * stripe_product_price function
     *
     * @param array $request = [
     * 'description' => 'description',
     * 'metadata' => ['app_name' => 'BFSS'],
     * 'name' => 'name',
     * 'price_info' => [
     *      'billing_scheme' => 'billing_scheme',
     *      'metadata' => [
     *          'app_name' => 'BFSS'
     *      ],
     *      'recurring' => [
     *          'interval' => 'month',
     *          'interval_count' => 1,
     *          'usage_type' => 'licensed'
     *      ],
     *      'unit_amount_decimal' => 99.99 * 100
     * ],
     * ]
     * @return void
     */
    public function stripe_product_price($data)
    {
        $ret = [
            'success' => false,
            'message' => 'Something went wrong'
        ];

        $stripe = new \Stripe\StripeClient(env("APP_STRIPE_API_KEY"));

        if (isset($data['stripe_product_id'])) {
            // $stripe->products->update(
            //     $data['stripe_product_id'],
            //     // "name" => $data['app_name'],
            //     ['metadata' => ["app_name" => $data['metadata']['app_name']]
            //   );
        } else {
            // SEARCH FOR STRIPE PRODUCT USING EMAIL
            $products = $stripe->products->search([
                'query' => 'status:\'true\' AND \'name:\'' . $data['product_name'] . '\' AND metadata[\'app_name\']:\'' . $data['metadata']['app_name'] . '\'',
            ]);

            if ($products && count($products->data) > 0) {
                $ret = [
                    'success' => false,
                    'message' => 'Data already exist'
                ];
            } else {
                $product = $stripe->products->create([
                    // "id" => "prod_Lb9PSww9OWdokA",
                    // "object" => "product",
                    "active" => true,
                    // "created" => 1651251359,
                    // "default_price" => "price_1Ktx6rKZSUuelLLRJjygJIjj",
                    "description" => $data['description'],
                    "images" => [],
                    // "livemode" => false,
                    "metadata" => [
                        "app_name" => $data['metadata']['app_name']
                    ],
                    "name" => $data['name'],
                    // "package_dimensions" => null,
                    // "shippable" => null,
                    // "statement_descriptor" => null,
                    // "tax_code" => "txcd_20030000",
                    // "unit_label" => null,
                    // "updated" => 1651876386,
                    // "url" => null
                ]);

                if ($product) {
                    $price = $stripe->prices->create([
                        // "id" => "price_1LI3t2KZSUuelLLRjc7AyDsU",
                        // "object" => "price",
                        "active" => true,
                        "billing_scheme" => $data['price_info']["billing_scheme"],
                        // "created" => 1656997284,
                        "currency" => "usd",
                        // "custom_unit_amount" => null,
                        // "livemode" => false,
                        "lookup_key" => null,
                        "metadata" => [
                            'app_name' => $data['price_info']['metadata']['app_name']
                        ],
                        // "nickname" => null,
                        "product" => $product->id,
                        "recurring" => [
                            // "aggregate_usage" => null,
                            "interval" => $data['price_info']['recurring']['interval'],
                            "interval_count" => $data['price_info']['recurring']['interval_count'],
                            "usage_type" => $data['price_info']['recurring']['usage_type']
                        ],
                        // "tax_behavior" => "exclusive",
                        // "tiers_mode" => '24.95',
                        // "transform_quantity" => null,
                        // "type" => "recurring",
                        // "unit_amount" => "24.95",
                        "unit_amount_decimal" => $data['price_info']['unit_amount_decimal']
                    ]);
                }

                $ret = [
                    'success' => true,
                    'message' => 'Data added',
                    'data' => ['product' => $product, 'price' => $price]
                ];;
            }
        }

        return $ret;
    }

    /**
     * stripe_customer_subscription function
     *
     * @param array $data
     * @return void
     */
    public function stripe_customer_subscription($data)
    {
        $stripe = new \Stripe\StripeClient(env("APP_STRIPE_API_KEY"));

        $exp_date = explode('/', $data['credit_expiry']);
        $exp_month = $exp_date[0];
        $exp_year = (int) '20' . $exp_date[1];

        // CREATE TOKEN
        $token = $stripe->tokens->create([
            'card' => [
                'number' => $data['credit_card_number'],
                'exp_month' => $exp_month,
                'exp_year' => $exp_year,
                'cvc' => $data['credit_cvv'],
            ],
        ]);

        $token_id = $token->id;

        $customer = [];

        if (isset($data['stripe_customer_id'])) {
            $customer = $stripe->customers->update(
                $data['stripe_customer_id'],
                [
                    'description' => $data['description'],
                    'name' => $data['firstname'] . ' ' . $data['lastname'],
                    'email' => $data['email'],
                    'address' => [
                        'line1' =>  $data['billing_street_address1'],
                        'line2' => isset($data['billing_street_address2']) ? $data['billing_street_address2'] : "",
                        'city' => $data['billing_city'],
                        'country' => $data['billing_country'],
                        'postal_code' => $data['billing_zip'],
                        'state' => $data['billing_state'],
                    ],
                    'shipping' => [
                        'address' => [
                            'line1' =>  $data['billing_street_address1'],
                            'line2' => isset($data['billing_street_address2']) ? $data['billing_street_address2'] : "",
                            'city' => $data['billing_city'],
                            'country' => $data['billing_country'],
                            'postal_code' => $data['billing_zip'],
                            'state' => $data['billing_state'],
                        ],
                        'name' => $data['firstname'] . ' ' . $data['lastname'],
                        // 'carrier' => '',
                        'phone' => !empty($data['phone_number']) ? $data['phone_number'] : "",
                        // 'tracking_number' => ''
                    ],
                    'source' => $token_id,
                    "metadata" => [
                        "app_name" => $data['metadata']['app_name'],
                    ]
                ]
            );
        } else {
            $customer = $stripe->customers->create([
                'description' => $data['description'],
                'name' => $data['firstname'] . ' ' . $data['lastname'],
                'email' => $data['email'],
                'address' => [
                    'line1' =>  $data['billing_street_address1'],
                    'line2' => isset($data['billing_street_address2']) ? $data['billing_street_address2'] : "",
                    'city' => $data['billing_city'],
                    'country' => $data['billing_country'],
                    'postal_code' => $data['billing_zip'],
                    'state' => $data['billing_state'],
                ],
                'shipping' => [
                    'address' => [
                        'line1' =>  $data['billing_street_address1'],
                        'line2' => isset($data['billing_street_address2']) ? $data['billing_street_address2'] : "",
                        'city' => $data['billing_city'],
                        'country' => $data['billing_country'],
                        'postal_code' => $data['billing_zip'],
                        'state' => $data['billing_state'],
                    ],
                    'name' => $data['firstname'] . ' ' . $data['lastname'],
                    // 'carrier' => '',
                    'phone' => !empty($data['phone_number']) ? $data['phone_number'] : "",
                    // 'tracking_number' => ''
                ],
                'source' => $token_id,
                "metadata" => [
                    "app_name" => $data['metadata']['app_name']
                ]
            ]);
        }

        $subscription = $stripe->subscriptions->create([
            'customer' => $customer->id,
            'metadata' => [
                'app_name' => $data['metadata']['app_name'],
                'coupon' =>  isset($data['metadata']['coupon']) ? $data['metadata']['coupon'] : '',
                'coupon_type' =>  isset($data['metadata']['coupon_type']) ? $data['metadata']['coupon_type'] : '',
                'plan' =>  isset($data['metadata']['plan']) ? $data['metadata']['plan'] : '',
            ],
            'items' => [
                ['price' => $data['stripe_price_id']]
            ],
        ]);

        return ['customer' => $customer, 'subscription' => $subscription];
    }

    /** for stripe */

    public function generate_invoice($user_id)
    {
        $last_invoice = \App\UserPayment::where('user_id', $user_id)->orderBy('invoice_id', 'desc')->first();
        $last_num = 0;
        if ($last_invoice) {
            $last_num = $last_invoice->invoice_id != null ? $last_invoice->invoice_id + 1 : 00001;
        } else {
            $last_num = 00001;
        }
        $num = sprintf("%05d", $last_num);
        return $num;
    }

    public function advert_generate_invoice($advertisement_id = "")
    {
        $last_invoice = \App\AdvertPayment::where('advertisement_id', $advertisement_id)->orderBy('invoice_id', 'desc')->first();
        $last_num = 0;
        if ($last_invoice) {
            $last_num = $last_invoice->invoice_id != null ? $last_invoice->invoice_id + 1 : 00001;
        } else {
            $last_num = 00001;
        }
        $num = 'INV-' . sprintf("%05d", $last_num);
        return $num;
    }
}