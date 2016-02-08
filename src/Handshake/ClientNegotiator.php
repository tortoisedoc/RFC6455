<?php
namespace Ratchet\RFC6455\Handshake;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use GuzzleHttp\Psr7\Request;

class ClientNegotiator {
    /**
     * @var ResponseVerifier
     */
    private $verifier;

    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    private $defaultHeader;

    function __construct() {
        $this->verifier = new ResponseVerifier;

        $this->defaultHeader = new Request('GET', '', [
            'Connection'            => 'Upgrade'
          , 'Upgrade'               => 'websocket'
          , 'Sec-WebSocket-Version' => $this->getVersion()
          , 'User-Agent'            => "RatchetRFC/0.0.0"
        ]);
    }

    public function generateRequest(UriInterface $uri) {
        return $this->defaultHeader->withUri($uri)
            ->withHeader("Sec-WebSocket-Key", $this->generateKey());
    }

    public function validateResponse(RequestInterface $request, ResponseInterface $response) {
        return $this->verifier->verifyAll($request, $response);
    }

    public function generateKey() {
        $chars     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwzyz1234567890+/=';
        $charRange = strlen($chars) - 1;
        $key       = '';
        for ($i = 0; $i < 16; $i++) {
            $key .= $chars[mt_rand(0, $charRange)];
        }

        return base64_encode($key);
    }

    public function getVersion() {
        return 13;
    }
}