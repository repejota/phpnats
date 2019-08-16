<?php
declare(strict_types = 1);


namespace Nats\Error;

use Throwable;

/**
 * Occurs if the NATS connection is lost.
 *
 * @author Nicolai Agersbæk <na@zitcom.dk>
 *
 * @api
 */
class ConnectionLostException extends \RuntimeException implements ExceptionInterface
{
    
    /**
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(int $code = 0, Throwable $previous = null)
    {
        parent::__construct('Connection to NATS was lost', $code ?? 0, $previous);
    }
}
