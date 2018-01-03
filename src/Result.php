<?php

namespace romanzipp\Twitch;

use GuzzleHttp\Psr7\Response;
use romanzipp\Twitch\Helpers\Paginator;

class Result
{
    /**
     * Query successfull
     * @var boolean
     */
    private $success = false;

    /**
     * Guzzle exception, if present
     * @var null|Exception
     */
    private $exception = null;

    /**
     * Query result data
     * @var array
     */
    public $data = [];

    /**
     * Total amount of result data
     * @var integer
     */
    public $total = 0;

    /**
     * Twitch response pagination cursor
     * @var null|array
     */
    public $pagination;

    /**
     * Internal paginator
     * @var null|Paginator
     */
    public $paginator;

    /**
     * Constructor
     * @param null|Response  $response  HTTP response
     * @param boolean        $exception Exception, if present
     * @param null|Paginator $paginator Paginator, if present
     */
    public function __construct($response, $exception = false, $paginator = null)
    {
        $this->success = $response instanceof Response;

        if ($exception) {
            $this->exception = $exception;
        }

        $jsonResponse = $this->success ? @json_decode($response->getBody()) : null;

        if ($this->success && property_exists($jsonResponse, 'data')) {
            $this->data = $jsonResponse->data;
        }

        if ($this->success && property_exists($jsonResponse, 'total')) {
            $this->total = $jsonResponse->total;
        } else {
            $this->total = count((array) $this->data);
        }

        if ($this->success && property_exists($jsonResponse, 'pagination')) {
            $this->pagination = $jsonResponse->pagination;
        }

        $this->paginator = $paginator ?? Paginator::from($this);
    }

    /**
     * Returns wether the query was successfull
     * @return bool Success state
     */
    public function success(): bool
    {
        return $this->success;
    }

    /**
     * Returns the last HTTP or API error
     * @return string Error message
     */
    public function error(): string
    {
        if (!$this->exception || !$this->exception->hasResponse()) {
            return 'Twitch API Unavailable';
        }

        $exception = (string) $e->getResponse()->getBody();
        $exception = @json_decode($exception);

        if (property_exists($exception, 'message')) {
            $exception->message;
        }

        return strval($exception);
    }

    /**
     * Shifts the current result (Use for single user/video etc. query)
     * @return mixed Shifted data
     */
    public function shift()
    {
        if ($this->data) {

            $data = $this->data;

            return array_shift($data);
        }

        return null;
    }

    /**
     * Set the Paginator to fetch the first set of results
     * @return Paginator
     */
    public function first()
    {
        return $this->paginator->first();
    }

    /**
     * Set the Paginator to fetch the next set of results
     * @return Paginator
     */
    public function next()
    {
        return $this->paginator->next();
    }

    /**
     * Set the Paginator to fetch the last set of results
     * @return Paginator
     */
    public function back()
    {
        return $this->paginator->back();
    }
}
