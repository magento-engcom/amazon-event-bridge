<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmazonEventBridge\Console\Command;

use Magento\AmazonEventBridge\Model\Consumer\Sqs;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to fetch SQS messages
 */
class ReceiveMessage extends Command
{
    /**
     * @var Sqs
     */
    private $sqsConsumer;

    /**
     * @param Sqs $sqsConsumer
     */
    public function __construct(
        Sqs $sqsConsumer
    ) {
        $this->sqsConsumer = $sqsConsumer;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('eventbridge:sqs:receive');
        $this->addOption('queue', 'qu', InputOption::VALUE_REQUIRED, 'Queue to fetch message from');
        $this->addOption('delete', 'd', InputOption::VALUE_NONE, 'Delete the message after receive');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = $input->getOption('queue');
        $delete = $input->getOption('delete');
        $messages = $this->sqsConsumer->receiveMessage($queue);

        foreach($messages as $message){
            $output->writeln($message->getBody());
            if($delete) {
                $this->sqsConsumer->deleteMessage($queue, $message->getReceiptHandle());
            }
        }
    }
}
