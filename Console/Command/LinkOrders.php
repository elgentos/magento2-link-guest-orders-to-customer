<?php
/**
 * Copyright Elgentos BV. All rights reserved.
 * https://www.elgentos.nl/
 */

declare(strict_types=1);

namespace Elgentos\LinkGuestOrdersToCustomer\Console\Command;

use Elgentos\LinkGuestOrdersToCustomer\Service\Connector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;

class LinkOrders extends Command
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * @param Connector $connector
     * @param string|null $name
     */
    public function __construct(
        Connector $connector,
        string $name = null
    ) {
        parent::__construct($name);
        $this->connector = $connector;
    }

    /**
     * @inheritDoc
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {

        try {
            $result = $this->connector->connect();
            $output->writeln('Found ' . $result . ' orders that are not connected to customers.');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('elgentos:link-guest-orders');
        $this->setDescription('Link existing guest orders to new or existing customers based on e-mail address');
        parent::configure();
    }
}
