<?php
/*
Gibbon: the flexible, open school platform
Founded by Ross Parker at ICHK Secondary. Built by Ross Parker, Sandra Kuipers and the Gibbon community (https://gibbonedu.org/about/)
Copyright © 2010, Gibbon Foundation
Gibbon™, Gibbon Education Ltd. (Hong Kong)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.
 */

namespace Gibbon\Services\Payment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Minimal client for PayU GPO Europe REST API (hosted payment page).
 *
 * @see https://developers.payu.com/europe/api/
 */
class PayUEuropeClient
{
    /** @var string */
    private $baseUrl;

    /** @var string */
    private $posId;

    /** @var string */
    private $clientSecret;

    public function __construct(string $environment, string $posId, string $clientSecret)
    {
        $this->posId = $posId;
        $this->clientSecret = $clientSecret;
        $this->baseUrl = $environment === 'sandbox'
            ? 'https://secure.snd.payu.com'
            : 'https://secure.payu.com';
    }

    /**
     * @throws GuzzleException
     */
    public function getAccessToken(): string
    {
        $client = new Client(['timeout' => 30]);
        $response = $client->post($this->baseUrl.'/pl/standard/user/oauth/authorize', [
            'http_errors' => false,
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->posId,
                'client_secret' => $this->clientSecret,
            ],
        ]);

        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        if (!is_array($data) || empty($data['access_token'])) {
            error_log('PayU OAuth failed: HTTP '.$response->getStatusCode().' '.$body);
            throw new \RuntimeException('PayU OAuth token could not be obtained.');
        }

        return $data['access_token'];
    }

    /**
     * @return array{redirectUri: string, orderId: string, extOrderId: string, raw: array}
     *
     * @throws GuzzleException
     */
    public function createOrder(
        string $accessToken,
        string $continueUrl,
        string $notifyUrl,
        string $customerIp,
        string $description,
        string $currencyCode,
        int $totalAmountMinor,
        string $extOrderId,
        string $productName
    ): array {
        $client = new Client(['timeout' => 60]);
        $payload = [
            'continueUrl' => $continueUrl,
            'notifyUrl' => $notifyUrl,
            'customerIp' => $customerIp,
            'merchantPosId' => $this->posId,
            'description' => $description,
            'currencyCode' => strtoupper($currencyCode),
            'totalAmount' => (string) $totalAmountMinor,
            'extOrderId' => $extOrderId,
            'products' => [
                [
                    'name' => $productName,
                    'unitPrice' => (string) $totalAmountMinor,
                    'quantity' => '1',
                ],
            ],
        ];

        $response = $client->post($this->baseUrl.'/api/v2_1/orders', [
            'http_errors' => false,
            'allow_redirects' => false,
            'headers' => [
                'Authorization' => 'Bearer '.$accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        $status = $response->getStatusCode();

        if (!is_array($data)) {
            error_log('PayU create order: invalid JSON, HTTP '.$status.' '.$body);
            throw new \RuntimeException('PayU create order returned an invalid response.');
        }

        $statusCode = $data['status']['statusCode'] ?? '';
        if (($status === 201 || $status === 302) && $statusCode === 'SUCCESS' && !empty($data['redirectUri']) && !empty($data['orderId'])) {
            return [
                'redirectUri' => $data['redirectUri'],
                'orderId' => $data['orderId'],
                'extOrderId' => $extOrderId,
                'raw' => $data,
            ];
        }

        error_log('PayU create order failed: HTTP '.$status.' '.$body);
        $msg = $data['status']['statusDesc'] ?? $body;

        throw new \RuntimeException('PayU could not create an order: '.$msg);
    }

    /**
     * @return array|null Order record from PayU (first element of "orders") or null on failure
     *
     * @throws GuzzleException
     */
    public function getOrder(string $accessToken, string $orderId): ?array
    {
        $client = new Client(['timeout' => 30]);
        $response = $client->get($this->baseUrl.'/api/v2_1/orders/'.rawurlencode($orderId), [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer '.$accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        if ($response->getStatusCode() !== 200 || !is_array($data) || empty($data['orders'][0])) {
            error_log('PayU get order failed: HTTP '.$response->getStatusCode().' '.$body);

            return null;
        }

        return $data['orders'][0];
    }
}
