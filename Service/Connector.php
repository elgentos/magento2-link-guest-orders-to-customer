<?php
/**
 * Copyright Elgentos BV. All rights reserved.
 * https://www.elgentos.nl/
 */

declare(strict_types=1);

namespace Elgentos\LinkGuestOrdersToCustomer\Service;

use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class Connector implements ConnectorInterface
{
    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var OrderResource
     */
    protected $orderResource;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Iterator
     */
    protected $iterator;

    /**
     * Constructor
     *
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderResource $orderResource
     * @param OrderFactory $orderFactory
     * @param Iterator $iterator
     */
    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        OrderResource $orderResource,
        OrderFactory $orderFactory,
        Iterator $iterator
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->iterator = $iterator;
    }

    /**
     * @inheritDoc
     */
    public function connect(?string $email = ''): int
    {
        /** @var OrderCollection $orders */
        $orders = $this->orderCollectionFactory->create()
            ->addFieldToFilter('customer_id', ['null' => true]);

        if (!empty($email)) {
            $orders->addFieldToFilter('customer_email', ['eq' => $email]);
        }

        $select = $orders->getSelect();
        $select
            ->joinLeft(
                ['customer_entity' => $select->getConnection()->getTableName('customer_entity')],
                'main_table.customer_email = customer_entity.email',
                [
                    'main_customer_id' => 'customer_entity.entity_id',
                    'main_customer_email' => 'customer_entity.email'
                ]
            )->where('customer_entity.entity_id IS NOT NULL')
            ->group(
                'main_table.entity_id'
            );

        if ($orders->getSize() > 0) {
            $this->iterator->walk(
                $orders->getSelect(),
                [[$this, 'callbackLinkOrder']],
                [
                    'order' => $this->orderFactory->create()
                ]
            );
        }

        return $orders->getSize();
    }

    /**
     * Callback link orders
     *
     * @param array $args
     * @return void
     * @throws \Exception
     */
    public function callbackLinkOrder(array $args = []): void
    {
        /** @var Order $order */
        $order = clone $args['order'];
        $order->setData($args['row']);

        if ($order->getCustomerId() != $order->getData('main_customer_id')
            && !empty($order->getData('main_customer_id'))
        ) {
            $order->setCustomerId((int)$order->getData('main_customer_id'));
            $order->setCustomerIsGuest(0);
            $this->orderResource->saveAttribute($order, ['customer_id', 'customer_is_guest']);
        }
    }
}
