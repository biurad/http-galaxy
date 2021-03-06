<?php

declare(strict_types=1);

/*
 * This file is part of Biurad opensource projects.
 *
 * PHP version 7.2 and above required
 *
 * @author    Divine Niiquaye Ibok <divineibok@gmail.com>
 * @copyright 2019 Biurad Group (https://biurad.com/)
 * @license   https://opensource.org/licenses/BSD-3-Clause License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Biurad\Http\Factory;

use Biurad\Http\Interfaces\Psr17Interface;
use Biurad\Http\Request;
use Biurad\Http\Response;
use Biurad\Http\ServerRequest;
use Biurad\Http\Stream;
use Biurad\Http\UploadedFile;
use Biurad\Http\Uri;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * @author Divine Niiquaye Ibok <divineibok@gmail.com>
 */
class NyholmPsr7Factory implements Psr17Interface
{
    /** @var Psr17Factory */
    private $factory;

    public function __construct()
    {
        $this->factory = new Psr17Factory();
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        return (new Request($method, $uri))->withRequest($this->factory->createRequest($method, $uri));
    }

    /**
     * {@inheritdoc}
     */
    public function createResponse(int $code = 200, string $reasonPhrase = 'Ok'): ResponseInterface
    {
        return (new Response())->withResponse($this->factory->createResponse($code, $reasonPhrase));
    }

    /**
     * {@inheritdoc}
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if (empty($method)) {
            if (!empty($serverParams['REQUEST_METHOD'])) {
                $method = $serverParams['REQUEST_METHOD'];
            } else {
                throw new \InvalidArgumentException('Cannot determine HTTP method');
            }
        }

        return (new ServerRequest($method, $uri, [], null, '1.1', $serverParams))
            ->withRequest($this->factory->createServerRequest($method, $uri, $serverParams));
    }

    /**
     * {@inheritdoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        return (new Stream())->withStream($this->factory->createStream($content));
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile(string $file, string $mode = 'r'): StreamInterface
    {
        return (new Stream())->withStream($this->factory->createStreamFromFile($file, $mode));
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return (new Stream())->withStream($this->factory->createStreamFromResource($resource));
    }

    /**
     * {@inheritdoc}
     */
    public function createUploadedFile(
        StreamInterface $stream,
        ?int $size = null,
        int $error = \UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null
    ): UploadedFileInterface {
        if (null === $size) {
            $size = $stream->getSize();
        }

        return (new UploadedFile('', $size, $error, $clientFilename, $clientMediaType))
            ->withUploadFile($this->factory->createUploadedFile($stream, $size, $error, $clientFilename, $clientMediaType));
    }

    /**
     * {@inheritdoc}
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return (new Uri($uri))->withUri($this->factory->createUri($uri));
    }

    /**
     * {@inheritdoc}
     */
    public static function fromGlobalRequest(): ServerRequestInterface
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $self = new static();
        $serverRequestCreator = new ServerRequestCreator($self->factory, $self->factory, $self->factory, $self->factory);

        return (new ServerRequest($method, ''))->withRequest($serverRequestCreator->fromGlobals());
    }
}
