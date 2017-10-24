<?php

namespace Tonic\Behat\ParallelScenarioExtension\Event;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Process\Process;

/**
 * Class AbstractProcessEvent.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class FeatureResultEvent extends Event
{
    /**
     * @var int
     */
    private $result;

    /**
     * InputProcessEvent constructor.
     *
     * @param int $result
     */
    public function __construct(int $result)
    {
        $this->result = $result;
    }

    /**
     * @return int
     */
    public function getResult()
    {
        return $this->result;
    }
}
