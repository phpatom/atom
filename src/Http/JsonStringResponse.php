<?php


namespace Atom\Framework\Http;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\InjectContentTypeTrait;
use Laminas\Diactoros\Stream;

class JsonStringResponse extends Response
{
    use InjectContentTypeTrait;

    /**
     * @var mixed
     */
    private $payload;

    /**
     * @var int
     */
    private int $encodingOptions;

    public function __construct(
        string $data,
        int $status = 200,
        array $headers = []
    ) {
        $this->setPayload($data);
        $body = $this->createBodyFromJson($data);
        $headers = $this->injectContentType('application/json', $headers);
        parent::__construct($body, $status, $headers);
    }

    /**
     * @return string
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    private function createBodyFromJson(string $json): Stream
    {
        $body = new Stream('php://temp', 'wb+');
        $body->write($json);
        $body->rewind();

        return $body;
    }

    /**
     * @param mixed $data
     */
    private function setPayload(string $data): void
    {
        $this->payload = $data;
    }
}
