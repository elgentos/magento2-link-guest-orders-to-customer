<?php

declare(strict_types=1);

namespace Elgentos\LinkGuestOrdersToCustomer\Console\Command;

use Elgentos\LinkGuestOrdersToCustomer\Service\Connector;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

class LinkOrders extends Command
{
    private OutputInterface $output;

    private Connector $connector;

    public function __construct(
        public OrderCollectionFactory $orderCollectionFactory,
        public PurchasedFactory $purchasedFactory,
        public CustomerRepositoryInterface $customerRepository,
        public OrderRepositoryInterface $orderRepository,
        $name = null
    ) {
        parent::__construct($name);
        $this->connector = new Connector($this);
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->output = $output;

        $orders = $this->orderCollectionFactory->create()
            ->addFieldToFilter('customer_id', ['null' => true]);

        $this->output->writeln('Found ' . $orders->getSize() . ' orders that are not connected to customers.');

        foreach ($orders as $order) {
            $this->connector->connectOrderToCustomer($order, $output);
        }

        return 0;
    }

    protected function configure()
    {
        $this->setName('elgentos:link-guest-orders');
        $this->setDescription('Link existing guest orders to new or existing customers based on e-mail address');
        parent::configure();
    }
}
