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

use Bldr\Application;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\StreamOutput;
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
            $append       = $this->call->has('append') && $this->call->append ? 'a' : 'w';
            $stream       = fopen($this->call->output, $append);
            $this->output = new StreamOutput($stream, StreamOutput::VERBOSITY_NORMAL, true);
        }

        /** @var FormatterHelper $formatter */
        $formatter = $this->helperSet->get('formatter');


        $this->findTokens($arguments);

        $builder = new ProcessBuilder($arguments);

        if ($this->call->has('cwd')) {
            $builder->setWorkingDirectory($this->call->cwd);
        }

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

        if ($this->call->has('failOnError') && $this->call->failOnError) {
            if ($this->call->has('successCodes') && !in_array($process->getExitCode(), $this->call->successCodes)) {
                throw new \Exception("Failed on the {$this->task->getName()} task.\n" . $process->getErrorOutput());
            }
        }
    }

    /**
     * Runs the tokenizer on all the arguments
     *
     * @param string[] $arguments
     */
    private function findTokens(array &$arguments)
    {
        foreach ($arguments as $index => $argument) {
            $arguments[$index] = $this->replaceTokens($argument);
        }
    }

    /**
     * Tokenizes the given string
     *
     * @param string $argument
     *
     * @return string
     */
    private function replaceTokens($argument)
    {
        $token_format = '/\$(.+)\$/';

        preg_match_all($token_format, $argument, $matches, PREG_SET_ORDER);

        if (sizeof($matches) < 1) {
            return $argument;
        }

        foreach ($matches as $match) {
            $argument = str_replace($match[0], Application::$$match[1], $argument);
        }

        return $argument;
    }
}
