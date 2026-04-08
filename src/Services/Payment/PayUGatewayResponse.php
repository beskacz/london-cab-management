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

/**
 * Adapter so PayU order fetch results match what {@see Payment::handlePaymentResponse} expects.
 */
class PayUGatewayResponse
{
    /** @var bool */
    private $successful;

    /** @var array */
    private $order;

    /** @var string|null */
    private $paymentId;

    public function __construct(bool $successful, array $order, ?string $paymentId)
    {
        $this->successful = $successful;
        $this->order = $order;
        $this->paymentId = $paymentId;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function isPending(): bool
    {
        $status = $this->order['status'] ?? '';

        return in_array($status, ['PENDING', 'WAITING_FOR_CONFIRMATION'], true);
    }

    public function getCode(): int
    {
        return $this->successful ? 200 : 400;
    }

    public function getMessage(): string
    {
        return $this->successful ? 'OK' : (string) ($this->order['status'] ?? 'FAILED');
    }

    public function getData(): array
    {
        return [
            'payu_orderId' => $this->order['orderId'] ?? '',
            'payu_orderStatus' => $this->order['status'] ?? '',
            'payu_totalAmount' => $this->order['totalAmount'] ?? '0',
            'payu_paymentId' => $this->paymentId,
        ];
    }
}
