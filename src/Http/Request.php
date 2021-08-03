<?php


namespace Atom\Framework\Http;

use Atom\Routing\MatchedRoute;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Negotiation\Accept;
use Negotiation\Negotiator;
use Psr\Http\Message\ServerRequestInterface;

class Request extends ServerRequest
{
    use ConvertMimeTypeToFormat;

    /**
     * @var Accept[]
     */
    private ?array $acceptedContentTypes = null;

    public static function incoming(): Request
    {
        return self::from(ServerRequestFactory::fromGlobals());
    }

    public static function from(ServerRequestInterface $request): Request
    {
        $new = new self(
            $request->getServerParams(),
            $request->getUploadedFiles(),
            $request->getUri(),
            $request->getMethod(),
            $request->getBody(),
            $request->getHeaders(),
            $request->getCookieParams(),
            $request->getQueryParams(),
            $request->getParsedBody(),
            $request->getProtocolVersion()
        );
        foreach ($request->getAttributes() as $k => $v) {
            $new = $new->withAttribute($k, $v);
        }
        return $new;
    }

    public function route(): ?MatchedRoute
    {
        return MatchedRoute::of($this);
    }

    public function isJson(): bool
    {
        $contentType = $this->getHeaderLine("content-type");
        return str_contains($contentType, '/json') || str_contains($contentType, '+json');
    }

    public function isMethod($method): bool
    {
        return strtolower($this->getMethod()) == strtolower($method);
    }

    /**
     * Determine if the current request probably expects a JSON response.
     *
     * @return bool
     */
    public function expectsJson(): bool
    {
        return ($this->isAjax() && !$this->isPjax() && $this->acceptsAnyContentType()) || $this->wantsJson();
    }

    /**
     * @return bool
     */
    public function isAjax(): bool
    {
        return $this->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * @return bool
     */
    public function isPjax(): bool
    {
        return $this->hasHeader('X-PJAX');
    }


    /**
     * @return bool
     */
    public function wantsJson(): bool
    {
        $acceptable = $this->getAcceptableContentTypes();
        if (empty($acceptable)) {
            return false;
        }
        foreach ($acceptable as $accepted) {
            $type = $accepted->getType();
            if (str_contains($type, '/json') || str_contains($type, '+json')) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $contentType
     * @return bool
     */
    public function accepts(string $contentType): bool
    {
        $accepts = $this->getAcceptableContentTypes();
        if (empty($accepts)) {
            return true;
        }
        foreach ($accepts as $accept) {
            $type = $accept->getType();
            if ($type === '*/*' || $type === '*') {
                return true;
            }
            if ($type === $contentType) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine if the current request accepts any content type.
     *
     * @return bool
     */
    public function acceptsAnyContentType(): bool
    {
        $acceptable = $this->getAcceptableContentTypes();

        return count($acceptable) === 0 || (
                isset($acceptable[0]) && ($acceptable[0]->getType() === '*/*' || $acceptable[0]->getType() === '*')
            );
    }

    /**
     * Determines whether a request accepts JSON.
     *
     * @return bool
     */
    public function acceptsJson(): bool
    {
        return $this->accepts('application/json');
    }

    /**
     * Determines whether a request accepts HTML.
     *
     * @return bool
     */
    public function acceptsHtml(): bool
    {
        return $this->accepts('text/html');
    }

    /**
     * @return Accept[]
     */
    private function getAcceptableContentTypes(): array
    {
        if (is_null($this->acceptedContentTypes)) {
            $negotiator = new Negotiator();
            $acceptHeader = $this->getHeaderLine("Accept");
            if ($acceptHeader == "") {
                return [];
            }
            $this->acceptedContentTypes = $negotiator->getOrderedElements($acceptHeader) ?? [];
        }
        return $this->acceptedContentTypes;
    }
}
