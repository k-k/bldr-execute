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

/**
 * @author Aaron Scherer <aaron@undergroundelephant.com>
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
        $command = '';
        foreach ($arguments as $argument) {
            $command .= $argument . ' ';
        }

        ob_implicit_flush(true);
        @ob_end_flush();
        flush();

        $descriptorspec = array(
            0 => array("pipe", "r"), // stdin is a pipe that the child will read from
            1 => array("pipe", "w"), // stdout is a pipe that the child will write to
            2 => array("pipe", "w") // stderr is a pipe that the child will write to
        );
        $pipes = [];

        $process = proc_open($command, $descriptorspec, $pipes);

        /** @var FormatterHelper $formatter */
        $formatter = $this->helperSet->get('formatter');

        $this->output->write([$formatter->formatSection($this->taskName, $arguments[0]), "\n"]);
        if (is_resource($process)) {
            while ($s = fgets($pipes[1])) {
                $this->output->write($formatter->formatSection($this->taskName, $s));
            }
        }

        $status = proc_close($process);

        if ($this->failOnError && !in_array($status, $this->successStatusCodes)) {
            throw new \Exception("Failed on the $this->taskName task.");
        }
    }
}
