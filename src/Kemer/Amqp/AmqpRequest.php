<?php
namespace Kemer\Amqp;

use Slim\Http as SlimHttp;

/**
 * Representation of an incoming, server-side HTTP request.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 *
 * Additionally, it encapsulates all data as it has arrived to the
 * application from the CGI and/or PHP environment, including:
 *
 * - The values represented in $_SERVER.
 * - Any cookies provided (generally via $_COOKIE)
 * - Query string arguments (generally via $_GET, or as parsed via parse_str())
 * - Upload files, if any (as represented by $_FILES)
 * - Deserialized body parameters (generally from $_POST)
 *
 * $_SERVER values MUST be treated as immutable, as they represent application
 * state at the time of request; as such, no methods are provided to allow
 * modification of those values. The other values provide such methods, as they
 * can be restored from $_SERVER or the request body, and may need treatment
 * during the application (e.g., body parameters may be deserialized based on
 * content type).
 *
 * Additionally, this interface recognizes the utility of introspecting a
 * request to derive and match additional parameters (e.g., via URI path
 * matching, decrypting cookie values, deserializing non-form-encoded body
 * content, matching authorization headers to users, etc). These parameters
 * are stored in an "attributes" property.
 *
 * Requests are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 */
class AmqpRequest extends SlimHttp\Request
{
    /**
     * Create new HTTP request with data extracted from the application
     * Environment object
     *
     * @param  Environment $environment The Slim application Environment
     *
     * @return self
     */
    public static function createFromEnvelope(Envelope $envelope)
    {
        $method = "POST";
        $uri = SlimHttp\Uri::createFromString(
            sprintf("%s", $envelope->getRoutingKey())
        );
        $headers = new SlimHttp\Headers($envelope->getHeaders());
        $headers["Content-Type"] = $envelope->getContentType();
        $cookies = SlimHttp\Cookies::parseHeader($headers->get('Cookie', []));
        $serverParams = [
            "app-id" => $envelope->getAppId()
        ];
        $body = new SlimHttp\RequestBody($envelope->getBody());

        $request = new static($method, $uri, $headers, $cookies, $serverParams, $body);
        return $request;
    }
}
