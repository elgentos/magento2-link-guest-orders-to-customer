<?php

/**
 * Copyright Elgentos BV. All rights reserved.
 * https://www.elgentos.nl/
 */

declare(strict_types=1);

namespace Elgentos\LinkGuestOrdersToCustomer\Service;

use Elgentos\LinkGuestOrdersToCustomer\Console\Command\LinkOrders;
use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Symfony\Component\Console\Output\OutputInterface;

class Connector
{
    private LinkOrders $linkOrders;

    public function __construct(LinkOrders $linkOrders)
    {
        $this->linkOrders = $linkOrders;
    }

    public function connectOrderToCustomer(Order $order, OutputInterface $output = null): void
    {
        if ($output?->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $output?->writeln(
                'Trying to connect order ' . $order->getIncrementId() . ' to customer ' .
                $order->getCustomerEmail()
            );
        }

        try {
            $customer = $this->linkOrders->customerRepository->get($order->getCustomerEmail());
            if ($order->getIncrementId() && $customer->getId()) {
                $order->setCustomerId($customer->getId());
                $order->setCustomerIsGuest(0);
                $this->linkOrders->orderRepository->save($order);

                // Support for downloadables
                // @phpstan-ignore-next-line
                $purchased = $this->linkOrders->purchasedFactory->create()->load(
                    $order->getIncrementId(),
                    'order_increment_id'
                );

                if ($purchased->getId()) {
                    $purchased->setCustomerId($customer->getId());
                    // @phpstan-ignore-next-line
                    $purchased->save();
                }

                $output?->writeln(
                    'Connected order ' . $order->getIncrementId() . ' / ' .
                    $order->getCustomerEmail() . '  to customer ' . $customer->getEmail()
                );
            }
        } catch (NoSuchEntityException $e) {
            if ($output?->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $output?->writeln(
                    'Customer not found - cannot connect order ' .
                    $order->getIncrementId() . ' to customer ' . $order->getCustomerEmail()
                );
            }
        } catch (Exception $e) {
            $output?->writeln($e->getMessage());
        }
    }
}
