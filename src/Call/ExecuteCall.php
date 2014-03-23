<?php

/**
 * This file is part of Bldr.io
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE
 */

namespace Bldr\Extension\Execute\Call;

use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class ExecuteCall extends \Bldr\Call\AbstractCall
{
    /**
     * {@inheritDoc}
     *
     * Logic obtained from http://stackoverflow.com/a/6144213/248903
     */
    public function run(array $arguments)
    {
        if ($this->call->has('output')) {
            $append = $this->call->has('append') && $this->call->append ? 'a' : 'w';
            $stream = fopen($this->call->output, $append);
            $this->output = new StreamOutput($stream, StreamOutput::VERBOSITY_NORMAL, true);
        }

        /** @var FormatterHelper $formatter */
        $formatter = $this->helperSet->get('formatter');

        $builder = new ProcessBuilder($arguments);
        $process = $builder->getProcess();

        if (get_class($this) === 'Bldr\Extension\Execute\Call\ExecuteCall') {
            $this->output->writeln(
                [
                    "",
                    $formatter->formatSection($this->task->getName(), 'Starting'),
                    ""
                ]
            );
        }

        $process->run(
            function ($type, $buffer) {
                $this->output->write($buffer);
            }
        );

        if ($this->failOnError && !in_array($process->getExitCode(), $this->successStatusCodes)) {
            throw new \Exception("Failed on the {$this->task->getName()} task.\n" . $process->getErrorOutput());
        }
    }
}
