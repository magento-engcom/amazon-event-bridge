<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmazonEventBridge\Model\Consumer;

use Aws\Sqs\Exception\SqsException;
use Aws\Sqs\SqsClient;
use Magento\AmazonEventBridge\Model\Consumer\Data\SqsMessage;
use Magento\AmazonEventBridge\Model\Consumer\Data\SqsMessageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for interacting with Amazon SQS
 */
class Sqs
{
    /**
     * System setting paths for API configuration values
     */
    const API_VERSION_KEY = 'amazon/api/sqs/version';
    const API_REGION_KEY = 'amazon/api/sqs/region';
    const API_KEY_KEY = 'amazon/api/sqs/key';
    const API_SECRET_KEY = 'amazon/api/sqs/secret';

    /**
     * @var SqsClient
     */
    private $client;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SqsMessageFactory
     */
    private $sqsMessageFactory;

    /**
     * @param SqsMessageFactory $sqsMessageFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        SqsMessageFactory $sqsMessageFactory,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->sqsMessageFactory = $sqsMessageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * Fetch message from SQS queue
     *
     * @param string $queueUrl
     * @param int $maxNumMessages
     * @return SqsMessage[]
     */
    public function receiveMessage(string $queueUrl, int $maxNumMessages = 1): array
    {
        $client = $this->getClient();
        try {
            $response = $client->receiveMessage([
                'QueueUrl' => $queueUrl,
                'MaxNumberOfMessages' => $maxNumMessages,
            ]);
        }  catch (SqsException $e){
            $this->logger->error('SQS receive message error.', [$e->getMessage()]);
            return [];
        }

        $messages = $response->get('Messages');
        $result = [];
        foreach($messages as $message){
            $result[] = $this->sqsMessageFactory->create([
                'data' => [
                    'messageId' => $message['MessageId'],
                    'body' => $message['Body'],
                    'receiptHandle' => $message['ReceiptHandle'],
                ]
            ]);
        }

        return $result;
    }

    /**
     * Remove message from queue
     *
     * @param string $queueUrl
     * @param string $messageReceiptHandle
     * @return bool
     */
    public function deleteMessage(string $queueUrl, string $messageReceiptHandle): bool
    {
        $client = $this->getClient();
        try {
            $client->deleteMessage([
                'QueueUrl' => $queueUrl,
                'ReceiptHandle' => $messageReceiptHandle
            ]);
        } catch(SqsException $e){
            $this->logger->error('SQS delete message error.', [$e->getMessage()]);
            return false;
        }

        return true;
    }

    /**
     * Get SQS client
     *
     * @return SqsClient
     */
    private function getClient(): SqsClient
    {
        if (empty($this->client)) {
            $this->client = new SqsClient([
                'version' => $this->scopeConfig->getValue(self::API_VERSION_KEY),
                'region' => $this->scopeConfig->getValue(self::API_REGION_KEY),
                'credentials' => [
                    'key' => $this->scopeConfig->getValue(self::API_KEY_KEY),
                    'secret' => $this->scopeConfig->getValue(self::API_SECRET_KEY)
                ]
            ]);
        }

        return $this->client;
    }
}
