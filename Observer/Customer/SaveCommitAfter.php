<?php

declare(strict_types=1);

namespace Elgentos\LinkGuestOrdersToCustomer\Observer\Customer;

class SaveCommitAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $data = $observer->getEvent()->getData();
    }
}
