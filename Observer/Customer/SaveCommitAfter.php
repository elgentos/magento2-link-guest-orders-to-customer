<?php

declare(strict_types=1);

namespace Elgentos\LinkGuestOrdersToCustomer\Observer\Customer;

use Elgentos\LinkGuestOrdersToCustomer\Service\Connector;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class SaveCommitAfter implements ObserverInterface
{
    public function __construct(
        public OrderCollectionFactory $orderCollectionFactory,
        public PurchasedFactory $purchasedFactory,
        public CustomerRepositoryInterface $customerRepository,
        public OrderRepositoryInterface $orderRepository,
        private Connector $connector,
    ) {
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
        $orders = $this->orderCollectionFactory->create()
            ->addFieldToFilter('customer_id', ['eq' => $observer->getEvent()->getCustomer()->getId()]);

        foreach ($orders as $order) {
            $this->connector->connectOrderToCustomer($order);
        }
    }
}
