<?php
/**
 * Copyright Elgentos BV. All rights reserved.
 * https://www.elgentos.nl/
 */

declare(strict_types=1);

namespace Elgentos\LinkGuestOrdersToCustomer\Observer\Customer;

use Elgentos\LinkGuestOrdersToCustomer\Service\Connector;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveCommitAfter implements ObserverInterface
{
    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @param Connector $connector
     */
    public function __construct(
        Connector $connector
    ) {
        $this->connector = $connector;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(
        Observer $observer
    ) {
        $customer = $observer->getEvent()->getCustomer();
        try {
            $this->connector->connect($customer->getEmail());
        } catch (\Exception $e) {//phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        }
    }
}
