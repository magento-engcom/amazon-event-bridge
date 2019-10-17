<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmazonEventBridge\Model;

use Magento\AmazonEventBridgeApi\Api\Data\EventInterface;
use Magento\AmazonEventBridgeApi\Api\PutEventsInterface;
use Aws\EventBridge\EventBridgeClient;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class PutEvents implements PutEventsInterface
{
    /**
     * System setting paths for API configuration values
     */
    const API_VERSION_KEY = 'amazon/api/eventbridge/version';
    const API_REGION_KEY = 'amazon/api/eventbridge/region';
    const API_KEY_KEY = 'amazon/api/eventbridge/key';
    const API_SECRET_KEY = 'amazon/api/eventbridge/secret';

    /**
     * @var EventBridgeClient
     */
    private $client;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Json $serializer
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(Json $serializer, ScopeConfigInterface $scopeConfig, LoggerInterface $logger)
    {
        $this->serializer = $serializer;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * @param EventInterface[] $events
     */
    public function putEvents(array $events): void
    {
        $client = $this->getClient();
        $entries = [];
        foreach($events as $event){
            try {
                $serializedDetail = $this->serializer->serialize($event->getDetail());
            } catch (\InvalidArgumentException $e) {
                $this->logger->error('Amazon Event Bridge Error', [$e->getMessage()]);
                continue;
            }
            $entry = [
                "Detail" => $serializedDetail,
                "DetailType" => $event->getDetailType(),
                "EventBusName" => $event->getEventBusName(),
                "Resources" => $event->getResources(),
                "Source" => $event->getSource(),
                "Time" => $event->getTime()
            ];
            $entry = array_filter($entry);

            $entries[] = $entry;
        }

        try {
            $response = $client->putEvents(['Entries' => $entries]);
        } catch(\Exception $e){
            $this->logger->error('Amazon Event Bridge Error', [$e->getMessage()]);
            return;
        }

        if($response->get('FailedEntryCount') > 0){
            foreach($response->get('Entries') as $error){
                $this->logger->error('Amazon Event Bridge Error', $error);
            }
        }
    }

    /**
     * Get AWS EventBridgeClient
     *
     * @return EventBridgeClient
     */
    private function getClient(): EventBridgeClient
    {
        if(empty($this->client)) {
            $this->client = new EventBridgeClient([
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
