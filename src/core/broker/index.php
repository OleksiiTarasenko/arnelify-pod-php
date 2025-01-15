<?php

class Broker {
    private bool $isDefaultConsumer = false;
    private array $actions = [];
    private array $req = [];
    private array $res = [];
    private $consumer;
    private $producer;

    public function __construct() {
        $this->setConsumer(function ($topic, $onMessage) {
            $this->defaultConsumer($topic, $onMessage);
        });

        $this->setProducer(function ($topic, $message) {
            $this->defaultProducer($topic, $message);
        });
    }

    private function getDateTime(): string {
        return (new DateTime())->format('Y-m-d H:i:s');
    }

    private function getRequestId(): string {
        $random = rand(10000, 19999);
        $milliseconds = round(microtime(true) * 1000);
        $code = $milliseconds . $random;

        return hash('sha256', $code);
    }

    private function requestHandler(string $topic): void {
        $this->consumer($topic . '-req', function ($message) use ($topic) {
            $ctx = json_decode($message, true);
            $ctx['receivedAt'] = $this->getDateTime();

            if (isset($this->actions[$topic])) {
                $action = $this->actions[$topic];
                $response = $action($ctx);

                $res = [
                    'content' => $response,
                    'createdAt' => $ctx['createdAt'] ?? null,
                    'receivedAt' => $this->getDateTime(),
                    'requestId' => $ctx['requestId'] ?? null,
                    'topic' => $ctx['topic'] ?? null,
                ];

                $this->sendResponse($topic, $res);
            }
        });
    }

    private function responseHandler(string $topic): void {
        $this->consumer($topic . '-res', function ($message) {
            $response = json_decode($message, true);
            $requestId = $response['requestId'];

            if (isset($this->res[$requestId])) {
                $callback = $this->res[$requestId];
                unset($this->res[$requestId]);
                $callback($response['content']);
            }
        });
    }

    private function sendResponse(string $topic, array $response): void {
        $serialized = json_encode($response);
        $this->producer($topic . '-res', $serialized);
    }

    private function getResponse(string $topic, array $params): array {
        $requestId = $this->getRequestId();
        $ctx = [
            'topic' => $topic,
            'requestId' => $requestId,
            'createdAt' => $this->getDateTime(),
            'receivedAt' => null,
            'params' => $params,
        ];

        $message = json_encode($ctx);
        $this->res[$requestId] = function ($response) use (&$result) {
            $result = $response;
        };

        $this->producer($topic . '-req', $message);

        // Псевдоасинхронна обробка
        while (!isset($result)) {
            usleep(100); // Чекаємо, поки відповідь не буде отримана
        }

        return $result;
    }

    public function defaultConsumer(string $topic, callable $onMessage): void {
        $this->isDefaultConsumer = true;
        $this->req[$topic] = $onMessage;
    }

    public function defaultProducer(string $topic, string $message): void {
        if (isset($this->req[$topic])) {
            $this->req[$topic]($message);
        }
    }

    public function setConsumer(callable $consumer): void {
        $this->consumer = $consumer;
    }

    public function setProducer(callable $producer): void {
        $this->producer = $producer;
    }

    public function subscribe(string $topic, callable $action): void {
        $this->actions[$topic] = $action;
        $this->requestHandler($topic);
        $this->responseHandler($topic);

        if ($this->isDefaultConsumer) {
            echo "Topic registered: '{$topic}'\n";
        }
    }

    public function call(string $topic, array $params): array {
        return $this->getResponse($topic, $params);
    }
}

// Приклад використання
$broker = new Broker();

$broker->subscribe('example_topic', function ($ctx) {
    return [
        'message' => 'Hello, ' . ($ctx['params']['name'] ?? 'Guest') . '!',
    ];
});

$response = $broker->call('example_topic', ['name' => 'World']);
echo $response['message']; // Output: Hello, World!

?>
