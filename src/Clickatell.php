<?php
/**
 * PHP SMS
 *
 * @author    Jacques Marneweck <jacques@siberia.co.za>
 * @copyright 2020-2021 Jacques Marneweck.  All rights strictly reserved.
 */

declare(strict_types=1);

namespace Jacques\SMS;

use Jacques\SMS\Clickatell\Feature;

class Clickatell
{
    /**
     * @var string
     */
    const VERSION = '0.7';

    /**
     * @var
     */
    protected \GuzzleHttp\Client $client;

    /**
     * Error codes generated by Clickatell Gateway.
     */
    protected array $errors = [
        '001' => 'Authentication failed',
        '002' => 'Unknown username or password',
        '003' => 'Session ID expired',
        '004' => 'Account frozen',
        '005' => 'Missing session ID',
        '007' => 'IP lockdown violation',
        '101' => 'Invalid or missing parameters',
        '102' => 'Invalid UDH. (User Data Header)',
        '103' => 'Unknown apismgid (API Message ID)',
        '104' => 'Unknown climsgid (Client Message ID)',
        '105' => 'Invalid Destination Address',
        '106' => 'Invalid Source Address',
        '107' => 'Empty message',
        '108' => 'Invalid or missing api_id',
        '109' => 'Missing message ID',
        '110' => 'Error with email message',
        '111' => 'Invalid Protocol',
        '112' => 'Invalid msg_type',
        '113' => 'Max message parts exceeded',
        '114' => 'Cannot route message',
        '115' => 'Message Expired',
        '116' => 'Invalid Unicode Data',
        '120' => 'Invalid delivery date',
        '121' => 'Destination mobile number blocked',
        '122' => 'Destination mobile opted out',
        '123' => 'Invalid Sender ID',
        '128' => 'Number delisted',
        '130' => 'Maximum MT limitexceeded until <UNIXTIME STAMP>',
        '201' => 'Invalid batch ID',
        '202' => 'No batch template',
        '301' => 'No credit left',
        '302' => 'Max allowed credit',
        '605' => 'Tokenpay transaction successful',
        '606' => 'Invalid Token',
        '901' => 'Internal error – please retry',
    ];

    /**
     * @var array
     */
    protected array $message_statuses = [
        '001' => 'Message unknown',
        '002' => 'Message queued',
        '003' => 'Delivered',
        '004' => 'Received by recipient',
        '005' => 'Error with message',
        '006' => 'User cancelled message delivery',
        '007' => 'Error delivering message',
        '008' => 'OK',
        '009' => 'Routing error',
        '010' => 'Message expired',
        '011' => 'Message queued for later delivery',
        '012' => 'Out of credit',
        '013' => 'Clickatell canceled message delivery',
        '014' => 'Maximum MT limit exceeded',
        '015' => 'Do not contact receiver',
    ];

    /**
     * Message types.  Clickatell sets the UDH for messages on their side.
     *
     * @var array
     */
    protected array $message_types = [
        'SMS_TEXT', /* This is the default message type. It is optional to specify this parameter. */
        'SMS_FLASH', /* To send an SMS that displays immediately upon arrival at the phone. */
        'SMS_NOKIA_OLOGO',
        'SMS_NOKIA_GLOGO',
        'SMS_NOKIA_PICTURE',
        'SMS_NOKIA_RINGTONE',
        'SMS_NOKIA_RTTL',
        'SMS_NOKIA_CLEAN',
        'SMS_NOKIA_VCARD',
        'SMS_NOKIA_VCAL',
    ];

    /**
     * Optional features.
     */

    /**
     * Delivery Acknowledgements.
     *
     * @var bool
     */
    public bool $deliv_ack = false;

    /**
     * Default options.
     */
    private array $options = [
        'scheme' => 'https',
        'hostname' => 'api.clickatell.com',
        'port'  => 443,
        'debug' => false,
        'username' => null,
        'password' => null,
        'api_id' => null,
    ];

    /**
     * Clickatell API Server Session ID.
     *
     * @var string|null
     */
    private ?string $session_id = null;

    /**
     * Constructor.
     *
     * <code>
     * $sms = new \Jacques\SMS\Clickatell([
     *     'username' => 'username',
     *     'password' => 'sw0rdf1sh',
     *     'api_id' => 1_234_567,
     * ]);
     * </code>
     *
     * @param array $options
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    public function __construct(array $options)
    {
        if (\array_key_exists('username', $options)) {
            $this->options['username'] = $options['username'];
        } else {
            throw new \RuntimeException('Please pass in your Clickatell username.');
        }

        if (\array_key_exists('password', $options)) {
            $this->options['password'] = $options['password'];
        } else {
            throw new \RuntimeException('Please pass in your Clickatell password.');
        }

        if (\array_key_exists('api_id', $options)) {
            if (!\is_numeric($options['api_id'])) {
                throw new \InvalidArgumentException('Please pass in a numeric Clickatell api_id.');
            }
            $this->options['api_id'] = $options['api_id'];
        } else {
            throw new \RuntimeException('Please pass in your Clickatell api_id.');
        }

        $this->client = new \GuzzleHttp\Client(
            [
                'base_uri' => \sprintf(
                    '%s://%s:%s/',
                    $this->options['scheme'],
                    $this->options['hostname'],
                    $this->options['port']
                ),
                'verify'  => false,
                'headers' => [
                    'User-Agent'  => 'php-sms/'.self::VERSION.' '.\GuzzleHttp\default_user_agent(),
                ],
            ]
        );
    }

    /**
     * Get default header for auth.
     */
    public function getDefaultAuthParams(): array
    {
        return \is_null($this->session_id) ?
        [

            'user'     => $this->options['username'],
            'password' => $this->options['password'],
            'api_id'   => $this->options['api_id'],
        ] : [ 'session_id' => $this->session_id ];
    }

    /**
     * @return string|null
     */
    public function getSessionId(): ?string
    {
        return $this->session_id;
    }

    /**
     * @return bool
     */
    public function getDeliveryAck(): bool
    {
        return $this->deliv_ack;
    }

    /**
     * Turn on or off Delivery Acknowledgements.
     *
     * @param  bool $value
     *
     * @return void
     */
    public function setDeliveryAck(bool $value): void
    {
        $this->deliv_ack = $value;
    }

    /**
     * @param string $session_id Clickatell API Session ID
     *
     * @return void
     */
    public function setSessionId(string $session_id): void
    {
        $this->session_id = $session_id;
    }

    /**
     * Authenticate to the Clickatell API Server.
     *
     * @see https://docs.clickatell.com/archive/channels/sms-http-s-api-archived/http-sms-api-authentication/
     *
     * @return ((int|string)[]|bool)
     *
     * @psalm-return array{status: string, http_code: int, body: string}|bool
     */
    public function auth()
    {
        $response = $this->client->post(
            '/http/auth',
            [
                'form_params' => [
                    'user'     => $this->options['username'],
                    'password' => $this->options['password'],
                    'api_id'   => $this->options['api_id'],
                ],
            ]
        );

        $body = (string) $response->getBody();

        $session = \preg_split('#:#', $body);

        if ('OK' === $session['0']) {
            $this->session_id = \trim($session['1']);

            return true;
        }

        return false;
    }

    /**
     * Query balance of remaining SMS credits.
     *
     * @see    https://docs.clickatell.com/archive/channels/sms-http-s-api-archived/http-api-retrieve-account-balance/
     *
     * @throws \GuzzleHttp\Exception\ClientException
     * @throws \GuzzleHttp\Exception\ServerException
     *
     * @return ((int|string)[]|false|string)
     *
     * @psalm-return array{status: string, http_code: int, body: string}|false|string
     */
    public function balance()
    {
        $response = $this->client->post(
            '/http/getbalance',
            [
                'form_params' => $this->getDefaultAuthParams(),
            ]
        );

        $body = (string) $response->getBody();

        $balance_response = \preg_split('#:#', $body);

        if ('Credit' === $balance_response['0']) {
            return \trim($balance_response['1']);
        } elseif ('ERR' === $balance_response['0']) {
            throw new \Exception($body);
        }
    }

    /**
     * Delete message queued by Clickatell which has not been passed
     * onto the SMSC.
     *
     * @since   1.14
     * @see     http://www.clickatell.com/downloads/Clickatell_http_2.2.2.pdf
     */
    public function delete_message(string $apimsgid)
    {
        $response = $this->client->post(
            '/http/delmsg',
            [
                'form_params' => \array_merge(
                    $this->getDefaultAuthParams(),
                    [
                        'apimsgid' => \trim($apimsgid)
                    ]
                )
            ]
        );

        $body = (string) $response->getBody();

        $delete_response = \preg_split("#[\\s:]+#", $body);

        if ($delete_response[2] == 'charge') {
            return [$delete_response[3], $delete_response[5]];
        }

        if ('Credit' === $delete_response['0']) {
            return \trim($delete_response['1']);
        } elseif ('ERR' === $delete_response['0']) {
            throw new \Exception($body);
        }
    }

    /**
     * Determine the cost of the message which was sent.
     *
     * @param string $api_msg_id
     *
     * @throws \GuzzleHttp\Exception\ClientException
     * @throws \GuzzleHttp\Exception\ServerException
     */
    public function get_message_charge(string $apimsgid)
    {
        if (32 != \strlen($apimsgid)) {
            throw new \InvalidArgumentException('Invalid API Message Id.');
        }

        $response = $this->client->post(
            '/http/getmsgcharge',
            [
                'form_params' => \array_merge(
                    $this->getDefaultAuthParams(),
                    [
                        'apimsgid' => \trim($apimsgid)
                    ]
                )
            ]
        );

        $body = (string) $response->getBody();

        $charge_response = \preg_split('#[\s:]+#', $body);

        if ($charge_response[2] == 'charge') {
            return [
                'api_msg_id' => $charge_response['1'],
                'charge' => $charge_response[3],
                'status' => $charge_response[5],
            ];
        } elseif ('ERR' === $charge_response['0']) {
            throw new \Exception($body);
        }
    }

    /**
     * Keep our session to the Clickatell API Server valid.
     *
     * @see https://docs.clickatell.com/archive/channels/sms-http-s-api-archived/http-api-ping/
     *
     * @throws \GuzzleHttp\Exception\ClientException
     * @throws \GuzzleHttp\Exception\ServerException
     *
     * @return ((int|string)[]|bool)
     *
     * @psalm-return array{status: string, http_code: int, body: string}|bool
     */
    public function ping(): bool
    {
        $response = $this->client->post(
            '/http/ping',
            [
                'form_params' => [
                    'session_id' => $this->session_id,
                ],
            ]
        );

        $body = (string) $response->getBody();

        $session = \preg_split('#:#', $body);
        return 'OK' === $session['0'];
    }

    /**
     * Query message status.
     *
     * @param string spimsgid generated by Clickatell API
     *
     * @return string message status or PEAR_Error object
     *
     * @since 1.5
     */
    public function query_message(string $apimsgid)
    {
        $response = $this->client->post(
            '/http/querymsg',
            [
                'form_params' => \array_merge(
                    $this->getDefaultAuthParams(),
                    [
                        'apimsgid' => $apimsgid,
                    ]
                ),
            ]
        );

        $body = (string) $response->getBody();

        $query_response = \preg_split('#:#', $body);
        if ('ID' === $query_response['0']) {
            return [true, \trim($query_response[(\is_countable($query_response) ? \count($query_response) : 0) - 1])];
        } elseif ('ERR' === $query_response['0']) {
            throw new \Exception($body);
        } else {
            return [false, 0];
        }
    }

    /**
     * Query if Clickatell can deliver to a destination and what that minimum
     * price is.
     *
     * @param   string  MSISDN to query
     *
     * @return mixed array with first element being true if routeable else false and second element is the message cost
     */
    public function routecoverage(string $msisdn)
    {
        $response = $this->client->post(
            '/utils/routeCoverage',
            [
                'form_params' => \array_merge(
                    $this->getDefaultAuthParams(),
                    [
                        'msisdn' => $msisdn
                    ]
                ),
            ]
        );

        $body = (string) $response->getBody();

        $route_response = \preg_split('#:#', $body);
        if ('OK' === $route_response['0']) {
            return [true, \trim($route_response[(\is_countable($route_response) ? \count($route_response) : 0) - 1])];
        } elseif ('ERR' === $route_response['0']) {
            throw new \Exception($body);
        } else {
            return [false, 0];
        }
    }

    /**
     * Send an SMS Message via the Clickatell API Server.
     *
     * @param array database result set
     *
     * @return mixed true on sucess or PEAR_Error object
     */
    public function send_message(array $args)
    {
        $params = [];

        $required = [
            'to',
            'message',
        ];

        /**
         * Mappings for generic fields to the fields the gateway uses.
         */
        $mappings = [
            'message' => 'text',
        ];

        foreach ($required as $field) {
            if (!\array_key_exists($field, $args) || empty($args[$field])) {
                throw new \RuntimeException(\sprintf("Required parameter '%s' is not set.", $field));
            }
            if (\array_key_exists($field, $mappings)) {
                $params[$mappings[$field]] = $args[$field];
            } else {
                $params[$field] = $args[$field];
            }
        }

        $optional = [
            'from',
            'climsgid',
            'mo',
            'max_credits',
            'escalate',
            'unicode',
            'udh',
            'data',
            'validity',
            'binary',
            'scheduled_time',
        ];

        foreach ($optional as $field) {
            if (isset($args[$field]) && !empty($args[$field])) {
                $params[$field] = $args[$field];
            }
        }

        if (\array_key_exists('msg_type', $args)) {
            if (!\in_array($args['msg_type'], $this->message_types)) {
                throw new \InvalidArgumentException(\sprintf("Invalid message type. Message Type is '%s'.", $args['msg_type']));
            }

            if ($args['msg_type'] !== 'SMS_TEXT') {
                $params['msg_type'] = $args['msg_type'];
            }
        } else {
            $args['msg_type'] = 'SMS_TEXT';
        }

        /*
         * Check if we are using a queue when sending as each account
         * with Clickatell is assigned three queues namely 1, 2 and 3.
         */
        if (isset($args['queue']) && \is_numeric($args['queue']) && \in_array($args['queue'], \range(1, 3))) {
            $params['queue'] = $args['queue'];
        }

        /**
         * Required Features.  Clickatell may ammend the required features on their side
         * when you attempt to use alpha or numeric sender ids and you do not have any
         * sender ids registered on your account.
         */
        $params['req_feat'] = 0;
        /*
         * Normal text message
         */
        if ($args['msg_type'] === 'SMS_TEXT') {
            $params['req_feat'] += Feature::FEAT_TEXT;
        }

        /*
         * We set the sender id is alpha numeric or numeric
         * then we change the sender from data.
         */
        if (isset($args['from']) && !empty($args['from'])) {
            if (\is_numeric($args['from'])) {
                $params['req_feat'] += Feature::FEAT_NUMBER;
            } elseif (\is_string($args['from'])) {
                $params['req_feat'] += Feature::FEAT_ALPHA;
            }
        }

        /*
         * Flash Messaging
         */
        if ($args['msg_type'] === 'SMS_FLASH') {
            $params['req_feat'] += Feature::FEAT_FLASH;
        }
        /*
         * Delivery Acknowledgments
         */
        if ($this->deliv_ack) {
            $params['req_feat'] += Feature::FEAT_DELIVACK;
            $params['deliv_ack'] = 1;
            $params['callback'] = 7;
        }

        if (\strlen($args['message']) > 160) {
            $params['req_feat'] += Feature::FEAT_CONCAT;
        }

        /*
         * Must we escalate message delivery if message is stuck in
         * the queue at Clickatell?
         *//*
        if (isset($_msg['escalate']) && !empty($_msg['escalate'])) {
            if (is_numeric($_msg['escalate'])) {
                if (in_array($_msg['escalate'], range(1, 2))) {
                    $_post_data .= '&escalate='.$_msg['escalate'];
                }
            }
        }
*/

        $response = $this->client->post(
            '/http/sendmsg',
            [
                'form_params' => \array_merge(
                    $this->getDefaultAuthParams(),
                    $params
                )
            ]
        );

        $body = (string) $response->getBody();

        $sendmsg_response = \preg_split('#:#', $body);

        if ('ID' === $sendmsg_response['0']) {
            return [1, \trim($sendmsg_response['1'])];
        } elseif ('ERR' === $sendmsg_response['0']) {
            throw new \Exception($body);
        }
    }

    /**
     * Spend a clickatell voucher which can be used for topping up of
     * sub user accounts.
     *
     * @param   string  voucher number
     *
     * @since   1.22
     * @see     http://www.clickatell.com/downloads/Clickatell_http_2.2.4.pdf
     */
    public function tokenpay(string $token)
    {
        if (!\is_numeric($token) && 16 != \strlen($token)) {
            throw new \InvalidArgumentException('Invalid voucher number.');
        }

        $response = $this->client->post(
            '/http/token_pay',
            [
                'form_params' => \array_merge(
                    $this->getDefaultAuthParams(),
                    [
                        'token' => $token,
                    ]
                )
            ]
        );

        $body = (string) $response->getBody();

        $tokenpay_response = \preg_split('#:#', $body);

        if ('OK' === $tokenpay_response['0']) {
            return [1, \trim($tokenpay_response['1'])];
        } elseif ('ERR' === $tokenpay_response['0']) {
            throw new \Exception($body);
        }
    }
}

/* vim: set noet ts=4 sw=4 ft=php: : */
