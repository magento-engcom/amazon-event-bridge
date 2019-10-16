<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmazonEventBridge\Console\Command;

use Magento\AmazonEventBridgeApi\Api\PutEventsInterface;
use Magento\AmazonEventBridgeApi\Api\Data\EventInterfaceFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to test that it is possible to put events to Event Bridge
 */
class PutTestEvent extends Command
{
    /**
     * @var PutEventsInterface
     */
    private $putEvents;

    /**
     * @var EventInterfaceFactory
     */
    private $eventFactory;

    /**
     * @param PutEventsInterface $putEvents
     * @param EventInterfaceFactory $eventFactory
     */
    public function __construct(
        PutEventsInterface $putEvents,
        EventInterfaceFactory $eventFactory
    ) {
        $this->putEvents = $putEvents;
        $this->eventFactory = $eventFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('eventbridge:putevent');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $this->eventFactory->create([
            'data' => [
                'detail' => ['test' => date('Y-m-d h:i:s')],
                'detailType' => 'TestEvent',
                'source' => 'TestEventSource',
            ]
        ]);

        $this->putEvents->putEvents([$event]);

        $output->writeln('done');
    }
}
