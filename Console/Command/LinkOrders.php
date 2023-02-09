<?php

declare(strict_types=1);

namespace Elgentos\LinkGuestOrdersToCustomer\Console\Command;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

class LinkOrders extends Command
{
    public function __construct(
        public OrderCollectionFactory $orderCollectionFactory,
        public PurchasedFactory $purchasedFactory,
        public CustomerRepositoryInterface $customerRepository,
        public OrderRepositoryInterface $orderRepository,
        $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $orders = $this->orderCollectionFactory->create()
            ->addFieldToFilter('customer_id', ['null' => true]);

        $output->writeln('Found ' . $orders->getSize() . ' orders that are not connected to customers.');

        foreach ($orders as $order) {
            if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln('Trying to connect order ' . $order->getIncrementId() . ' to customer ' . $order->getCustomerEmail());
            }
            try {
                $customer = $this->customerRepository->get($order->getCustomerEmail());
                if ($order->getIncrementId() && $customer->getId()) {
                    $order->setCustomerId($customer->getId());
                    $order->setCustomerIsGuest(0);
                    $this->orderRepository->save($order);

                    // Support for downloadables
                    $purchased = $this->purchasedFactory->create()->load(
                        $order->getIncrementId(),
                        'order_increment_id'
                    );

                    if ($purchased->getId()) {
                        $purchased->setCustomerId($customer->getId());
                        $purchased->save();
                    }

                    $output->writeln('Connected order ' . $order->getIncrementId() . ' / ' . $order->getCustomerEmail() . '  to customer '. $customer->getEmail());
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $output->writeln('Customer not found - cannot connect order ' . $order->getIncrementId() . ' to customer ' . $order->getCustomerEmail());
                }
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
            }
        }
    }

    protected function configure()
    {
        $this->setName('elgentos:link-guest-orders');
        $this->setDescription('Link existing guest orders to newly created or existing customer based on e-mail address');
        parent::configure();
    }
}
