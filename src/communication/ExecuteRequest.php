<?php

namespace Yosmy\Payment\Gateway\Blackstone;

use Yosmy;
use Yosmy\Payment\Gateway;

/**
 * @di\service({
 *     private: true
 * })
 */
class ExecuteRequest
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @var string
     */
    private $appKey;

    /**
     * @var string
     */
    private $appType;

    /**
     * @var string
     */
    private $mid;

    /**
     * @var string
     */
    private $cid;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var Yosmy\Http\ExecuteRequest
     */
    private $executeRequest;

    /**
     * @var Yosmy\LogEvent
     */
    private $logEvent;

    /**
     * @di\arguments({
     *     environment: "%blackstone_environment%",
     *     appKey:      "%blackstone_app_key%",
     *     appType:     "%blackstone_app_type%",
     *     mid:         "%blackstone_mid%",
     *     cid:         "%blackstone_cid%",
     *     username:    "%blackstone_username%",
     *     password:    "%blackstone_password%"
     * })
     *
     * @param string                    $environment
     * @param string                    $appKey
     * @param string                    $appType
     * @param string                    $mid
     * @param string                    $cid
     * @param string                    $username
     * @param string                    $password
     * @param Yosmy\Http\ExecuteRequest $executeRequest
     * @param Yosmy\LogEvent            $logEvent
     */
    public function __construct(
        string $environment,
        string $appKey,
        string $appType,
        string $mid,
        string $cid,
        string $username,
        string $password,
        Yosmy\Http\ExecuteRequest $executeRequest,
        Yosmy\LogEvent $logEvent
    ) {
        $this->environment = $environment;
        $this->appKey = $appKey;
        $this->appType = $appType;
        $this->mid = $mid;
        $this->cid = $cid;
        $this->username = $username;
        $this->password = $password;
        $this->executeRequest = $executeRequest;
        $this->logEvent = $logEvent;
    }

    /**
     * @param string     $uri
     * @param array|null $params
     *
     * @return array
     *
     * @throws Gateway\ApiException
     */
    public function execute(
        string $uri,
        array $params = []
    ): array {
        $request = [
            'uri' => $uri,
            'params' => $params
        ];

        $params = array_merge(
            [
                'AppKey' => $this->appKey,
                'AppType' => $this->appType,
                'mid' => $this->mid,
                'cid' => $this->cid,
                'Username' => $this->username,
                'Password' => $this->password,
            ],
            $params
        );

        try {
            $response = $this->executeRequest->execute(
                'post',
                sprintf('%s%s', $this->environment, $uri),
                [
                    'form_params' => $params
                ]
            );

            $response = $response->getBody();

            $this->logEvent->log(
                [
                    'yosmy.payment.gateway.blackstone'
                ],
                [],
                [
                    'request' => $request,
                    'response' => $response
                ]
            );

            if ($response['ResponseCode'] != 200) {
                throw new Gateway\ApiException($response);
            }

            return $response;
        } catch (Yosmy\Http\Exception $e) {
            $response = $e->getResponse();

            $this->logEvent->log(
                [
                    'yosmy.payment.gateway.blackstone'
                ],
                [
                    'request' => $request,
                    'response' => $response
                ],
                []
            );

            throw new Gateway\ApiException($response);
        }
    }
}
