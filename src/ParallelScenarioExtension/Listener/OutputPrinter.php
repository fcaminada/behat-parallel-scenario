<?php

namespace Tonic\Behat\ParallelScenarioExtension\Listener;

use Behat\Testwork\Counter\Memory;
use Behat\Testwork\Counter\Timer;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tonic\Behat\ParallelScenarioExtension\Event\FeatureResultEvent;
use Tonic\Behat\ParallelScenarioExtension\Event\InputProcessEvent;
use Tonic\Behat\ParallelScenarioExtension\Event\ParallelScenarioEventType;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcess;
use Tonic\ParallelProcessRunner\Event\ProcessEvent;

/**
 * Class OutputPrinter.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class OutputPrinter implements EventSubscriberInterface
{
    /**
     * @var Timer
     */
    private $timer;

    /**
     * @var Memory
     */
    private $memory;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var integer
     */
    private $scenariosPrinted = 0;

    /**
     * @var integer
     */
    private $successScenarios = 0;

    /**
     * @var array
     */
    private $errorOutputList = array();

    /**
     * @var array
     */
    private $formats = array();

    /**
     * @param OutputInterface $output
     * @param OutputInterface $output
     */
    public function init(OutputInterface $output, InputInterface $input)
    {
        $this->output = $output;
        $this->input = $input;
        $this->timer = new Timer();
        $this->memory = new Memory();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ParallelScenarioEventType::PROCESS_BEFORE_START => 'beforeStart',
            ParallelScenarioEventType::PROCESS_AFTER_STOP => 'afterStop',
            ParallelScenarioEventType::EXECUTION_TESTED_BEFORE => 'beforeExecStart',
            ParallelScenarioEventType::EXECUTION_TESTED_AFTER => 'afterExecStop',
            ParallelScenarioEventType::FEATURE_TESTED_BEFORE => 'beforeFeatureStart',
        ];
    }

    /**
     * @param ProcessEvent $event
     */
    public function beforeStart(ProcessEvent $event)
    {
        // @TODO.
    }

    /**
     * @param ProcessEvent $event
     */
    public function afterStop(ProcessEvent $event)
    {
        /** @var ScenarioProcess $process */
        $process = $event->getProcess();
        if ($process->withError()) {
            $this->output->write('F');
            $this->errorOutputList[] = $process->getOutput();
        } else {
            $this->output->write('.');
            $this->successScenarios++;
        }

        if (++$this->scenariosPrinted % 70 == 0) {
            $this->output->writeln(' ' . $this->scenariosPrinted);
        }
    }

    /**
     * @param FeatureResultEvent $event
     */
    public function beforeExecStart(FeatureResultEvent $event)
    {
        $this->formats = $this->input->getOption('format');
        $this->output->writeln(sprintf('Executing scenarios with %d parallel processes...', $this->input->getOption('parallel-process')));
        $this->timer->start();
    }

    /**
     * @param FeatureResultEvent $event
     */
    public function afterExecStop(FeatureResultEvent $event)
    {
        $this->timer->stop();
        $this->output->writeln('');
        $this->output->writeln('');
        $this->output->write("Done excecuting parallel scenarios");
        if ($event->getResult() != 0) {
          $this->output->write(sprintf(" with %d errors:", count($this->errorOutputList)));
        }

        $this->output->writeln('');
        foreach ($this->errorOutputList as $errorOutput) {
          $this->output->writeln(sprintf('<error>%s</error>', $errorOutput));
        }

        $this->output->writeln('');
        $this->output->writeln('');

        // Write total statistics.
        $this->output->writeln(sprintf('%d process (%d passed)', $this->scenariosPrinted, $this->successScenarios));

        // Write memory and timer.
        $timer = $this->timer;
        $memory = $this->memory;
        $this->output->writeln(sprintf('%s (%s)', $timer, $memory));

    }

    /**
     * @param FeatureResultEvent $event
     */
    public function beforeFeatureStart(FeatureResultEvent $event)
    {
        // @TODO.
    }
}
