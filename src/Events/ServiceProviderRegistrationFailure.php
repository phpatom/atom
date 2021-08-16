<?php


namespace Atom\Framework\Events;

use Atom\Event\AbstractEvent;
use Atom\Framework\Contracts\ServiceProviderContract;
use Throwable;

class ServiceProviderRegistrationFailure extends AbstractEvent
{
    /**
     * @var ServiceProviderContract
     */
    private ServiceProviderContract $serviceProvider;
    /**
     * @var Throwable
     */
    private Throwable $exception;

    public function __construct(ServiceProviderContract $serviceProvider, Throwable $exception)
    {
        $this->serviceProvider = $serviceProvider;
        $this->exception = $exception;
    }

    /**
     * @return Throwable
     */
    public function getException(): Throwable
    {
        return $this->exception;
    }

    /**
     * @return ServiceProviderContract
     */
    public function getServiceProvider(): ServiceProviderContract
    {
        return $this->serviceProvider;
    }
}
