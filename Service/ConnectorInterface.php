<?php
/**
 * Copyright Elgentos BV. All rights reserved.
 * https://www.elgentos.nl/
 */

namespace Elgentos\LinkGuestOrdersToCustomer\Service;

interface ConnectorInterface
{
    /**
     * Return number of affected orders for given email address
     *
     * @param string|null $email
     * @return int
     */
    public function connect(?string $email = ''): int;
}
